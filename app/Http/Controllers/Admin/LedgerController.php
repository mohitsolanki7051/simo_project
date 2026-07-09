<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use App\Models\CreditNote;
use App\Models\DebitNote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use MongoDB\BSON\Decimal128;

class LedgerController extends Controller
{
    // =========================================================
    //  ENTRY POINT
    // =========================================================

    public function show(Request $request, string $partyType, string $id)
    {
        $party = $this->resolveParty($partyType, $id);

        $ledger     = $this->buildLedger($partyType, $id, $party);
        $itemReport = $this->buildItemReport($partyType, $id);
        $profile    = $this->buildProfile($partyType, $party);
        $summary    = $this->buildSummary($ledger, $partyType);

        return view('admin.ledger.show', compact(
            'party', 'partyType',
            'ledger', 'itemReport',
            'profile', 'summary'
        ));
    }

    // =========================================================
    //  LIGHTWEIGHT CLOSING BALANCE  (for list pages / dashboards)
    //
    //  Same debit/credit rules as buildLedger(), but only sums totals
    //  instead of building every row — much cheaper when you need the
    //  balance for many parties at once (e.g. the parties index table).
    //  Positive = Dr (party owes the business), Negative = Cr (business
    //  owes the party) — same convention as the full ledger.
    // =========================================================

    public static function getClosingBalance(string $partyType, string $id): float
    {
        $controller = new self();
        $party      = $controller->resolveParty($partyType, $id);
        $balance    = $controller->toFloat($party->opening_balance ?? 0);

        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {
            $balance += SalesInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->where('status', '!=', 'cancelled')
                ->get()
                ->sum(fn($inv) => $controller->toFloat($inv->grand_total));

            $balance -= $controller->getSalesPaymentsIn($id)
                ->sum(fn($pmt) => $controller->toFloat($pmt->amount));

            $balance -= CreditNote::where('party_id', $id)->get()
                ->sum(fn($cn) => $controller->toFloat($cn->amount));

            $balance += PurchasePayment::where('party_id', $id)
                ->where('payment_subtype', 'credit_refund')
                ->get()
                ->sum(fn($pmt) => $controller->toFloat($pmt->amount));
        }

        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {
            $balance -= PurchaseInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->where('status', '!=', 'cancelled')
                ->get()
                ->sum(fn($inv) => $controller->toFloat($inv->grand_total));

            $balance += $controller->getPurchasePaymentsOut($id)
                ->sum(fn($pmt) => $controller->toFloat($pmt->amount));

            $balance -= DebitNote::where('party_id', $id)->get()
                ->sum(fn($dn) => $controller->toFloat($dn->amount));

            $balance += SalesPayment::where('party_id', $id)
                ->where('payment_subtype', 'debit_refund')
                ->get()
                ->sum(fn($pmt) => $controller->toFloat($pmt->amount));
        }

        return round($balance, 2);
    }

    // Batch version — avoids N+1 if you're rendering a list of many parties.
    // Returns [ party_id => closing_balance ].
    public static function getClosingBalancesBatch(string $partyType, array $partyIds): array
    {
        $balances = [];
        foreach ($partyIds as $id) {
            $balances[(string) $id] = self::getClosingBalance($partyType, (string) $id);
        }
        return $balances;
    }

    public function print(Request $request, string $partyType, string $id)
    {
        $party = $this->resolveParty($partyType, $id);

        $fullLedger = $this->buildLedger($partyType, $id, $party);

        $fromDateRaw   = $request->get('from_date');   // e.g. 2026-04-01 (HTML date input format)
        $toDateRaw     = $request->get('to_date');     // e.g. 2026-06-27
        $invoiceType   = $request->get('invoice_type'); // 'gst' | 'cash' | null (= all)

        $ledger = $this->filterLedgerByDateRange($fullLedger, $fromDateRaw, $toDateRaw);

        if ($invoiceType && in_array($invoiceType, ['gst', 'cash'])) {
            $ledger = $this->filterLedgerByInvoiceType($ledger, $invoiceType);
        }

        $summary = $this->buildSummary($ledger, $partyType);

        $fromDate = $fromDateRaw ? Carbon::parse($fromDateRaw)->format('d-m-Y') : 'Opening';
        $toDate   = $toDateRaw   ? Carbon::parse($toDateRaw)->format('d-m-Y')   : now()->format('d-m-Y');

        return view('admin.ledger.print', compact(
            'party', 'partyType', 'ledger', 'summary', 'fromDate', 'toDate', 'invoiceType'
        ));
    }

    // =========================================================
    //  INVOICE-TYPE FILTER  (for "show me only GST" / "only Cash Memo")
    //
    //  Unlike the date-range filter, this does NOT carry forward an
    //  adjusted opening balance — a bill-type ledger is a different view
    //  of the SAME party, not a different time window. Opening balance
    //  stays at the party's original opening_balance (tagged 'all', so
    //  it's kept), and only rows matching the requested type — plus rows
    //  tagged 'all' (standalone payments, mixed allocations) — are kept.
    //  The closing balance therefore reads as "net effect of GST-only
    //  (or Cash-only) activity plus the original opening balance", which
    //  is the closest honest equivalent to a single-type ledger here.
    // =========================================================

    private function filterLedgerByInvoiceType(\Illuminate\Support\Collection $ledger, string $invoiceType): \Illuminate\Support\Collection
    {
        $kept = $ledger->filter(function ($row) use ($invoiceType) {
            $rowType = $row['invoice_type'] ?? 'all';
            return $rowType === 'all' || $rowType === $invoiceType;
        })->values();

        // Recompute running balance over just the kept rows.
        $result  = collect();
        $balance = 0.0;

        foreach ($kept as $row) {
            if ($row['is_opening']) {
                $balance = $row['balance'];
                $result->push($row);
                continue;
            }
            if ($row['is_closing']) {
                $row['balance'] = $balance;
                $result->push($row);
                continue;
            }
            $balance = round($balance + $row['debit'] - $row['credit'], 2);
            $row['balance'] = $balance;
            $result->push($row);
        }

        return $result;
    }

    // =========================================================
    //  DATE RANGE FILTER  (for print/PDF with custom period)
    //
    //  Opening Balance row gets recalculated to absorb every
    //  transaction that happened BEFORE `from`, so the running
    //  balance inside the selected window stays accurate — exactly
    //  how Tally / MyBillBook period-based statements behave.
    //  Transactions AFTER `to` are simply dropped; the Closing
    //  Balance row reflects the balance at the end of the window.
    // =========================================================

    private function filterLedgerByDateRange(\Illuminate\Support\Collection $fullLedger, ?string $fromDateRaw, ?string $toDateRaw): \Illuminate\Support\Collection
    {
        if (!$fromDateRaw && !$toDateRaw) {
            return $fullLedger;
        }

        $from = $fromDateRaw ? Carbon::parse($fromDateRaw)->startOfDay() : null;
        $to   = $toDateRaw   ? Carbon::parse($toDateRaw)->endOfDay()     : null;

        $openingBalance = 0.0;
        $windowRows      = collect();

        foreach ($fullLedger as $row) {
            if ($row['is_opening'] || $row['is_closing']) {
                continue; // we rebuild these ourselves below
            }

            $rowDate = $row['raw_date'] ? Carbon::parse($this->sortableDate($row['raw_date'])) : null;

            if ($from && $rowDate && $rowDate->lt($from)) {
                // Happened before the window — absorb into opening balance
                $openingBalance += $row['debit'] - $row['credit'];
                continue;
            }

            if ($to && $rowDate && $rowDate->gt($to)) {
                continue; // happened after the window — drop entirely
            }

            $windowRows->push($row);
        }

        // The very first ledger row carries the party's true opening_balance;
        // fold that in so totals stay correct even with no prior transactions.
        $trueOpeningBalance = $fullLedger->first()['balance'] ?? 0.0;
        $openingBalance += $trueOpeningBalance;

        $result  = collect();
        $balance = round($openingBalance, 2);

        $result->push([
            'date' => null, 'raw_date' => null, 'voucher_type' => 'Opening Balance',
            'sr_no' => '—', 'payment_mode' => '—', 'reference_number' => null,
            'debit' => 0.0, 'credit' => 0.0, 'balance' => $balance,
            'due_date' => null, 'due_status' => null, 'invoice_type' => 'all',
            'is_opening' => true, 'is_closing' => false,
        ]);

        // IMPORTANT: re-derive each row's running balance against the NEW
        // windowed opening balance — the value stored on $row['balance'] was
        // computed against the full-history opening and would be wrong here.
        foreach ($windowRows as $row) {
            $balance = round($balance + $row['debit'] - $row['credit'], 2);
            $row['balance'] = $balance;
            $result->push($row);
        }

        $result->push([
            'date' => null, 'raw_date' => null, 'voucher_type' => 'Closing Balance',
            'sr_no' => '—', 'payment_mode' => '—', 'reference_number' => null,
            'debit' => 0.0, 'credit' => 0.0,
            'balance' => $balance,
            'due_date' => null, 'due_status' => null, 'invoice_type' => 'all',
            'is_opening' => false, 'is_closing' => true,
        ]);

        return $result;
    }

    private function resolveParty(string $partyType, string $id)
    {
        return $partyType === 'vendor'
            ? Vendor::with('addresses')->findOrFail($id)
            : Customer::with('addresses')->findOrFail($id);
    }

    // =========================================================
    //  CORE LEDGER BUILDER  (Tally / MyBillBook style)
    //
    //  Rules:
    //  - ONE row per voucher (invoice, payment, credit/debit note, refund)
    //  - No splitting a payment into "opening balance" + "invoice" rows
    //  - Running balance = balance + debit - credit  (party's "amount due"
    //    convention — works the same whether party is customer or vendor,
    //    because each voucher type already carries the correct debit/credit)
    //  - Opening Balance row's `balance` IS the opening balance itself
    //    (not zero), matching MyBillBook.
    //  - Sale Invoice / Refund Payment / Debit Note  -> DEBIT  (raises due)
    //  - Purchase Invoice / Payment-in / Credit Note  -> CREDIT (lowers due)
    //    (signs flip appropriately for vendor side, see below)
    // =========================================================

    private function buildLedger(string $partyType, string $id, $party): \Illuminate\Support\Collection
    {
        $openingBalance = $this->toFloat($party->opening_balance ?? 0);
        $entries        = [];

        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {
            $entries = array_merge($entries, $this->buildSalesSideEntries($id));
        }

        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {
            $entries = array_merge($entries, $this->buildPurchaseSideEntries($id));
        }

        // Date ASC, and within same date keep invoices before their payments
        // (stable secondary sort by a type-priority so ledger reads naturally)
        usort($entries, function ($a, $b) {
            $dateCmp = strcmp($this->sortableDate($a['raw_date'] ?? null), $this->sortableDate($b['raw_date'] ?? null));
            if ($dateCmp !== 0) return $dateCmp;
            return ($a['_sort_priority'] ?? 9) <=> ($b['_sort_priority'] ?? 9);
        });

        $result  = collect();
        $balance = $openingBalance;

        $result->push([
            'date'            => null,
            'raw_date'        => null,
            'voucher_type'    => 'Opening Balance',
            'sr_no'           => '—',
            'payment_mode'    => '—',
            'reference_number'=> null,
            'debit'           => 0.0,
            'credit'          => 0.0,
            'balance'         => round($balance, 2),
            'due_date'        => null,
            'due_status'      => null,
            'invoice_type'    => 'all', // always visible regardless of GST/Cash filter
            'is_opening'      => true,
            'is_closing'      => false,
        ]);

        foreach ($entries as $entry) {
            $balance = round($balance + $entry['debit'] - $entry['credit'], 2);

            $result->push([
                'date'            => $entry['date'],
                'raw_date'        => $entry['raw_date'],
                'voucher_type'    => $entry['voucher_type'],
                'sr_no'           => $entry['sr_no'],
                'payment_mode'    => $entry['payment_mode'],
                'reference_number'=> $entry['reference_number'],
                'debit'           => $entry['debit'],
                'credit'          => $entry['credit'],
                'balance'         => $balance,
                'due_date'        => $entry['due_date'] ?? null,
                'due_status'      => $entry['due_status'] ?? null,
                'invoice_type'    => $entry['invoice_type'] ?? 'all',
                'is_opening'      => false,
                'is_closing'      => false,
            ]);
        }

        // Closing balance row — mirrors MyBillBook's final summary line
        $result->push([
            'date'            => null,
            'raw_date'        => null,
            'voucher_type'    => 'Closing Balance',
            'sr_no'           => '—',
            'payment_mode'    => '—',
            'reference_number'=> null,
            'debit'           => 0.0,
            'credit'          => 0.0,
            'balance'         => $balance,
            'due_date'        => null,
            'due_status'      => null,
            'invoice_type'    => 'all', // always visible regardless of GST/Cash filter
            'is_opening'      => false,
            'is_closing'      => true,
        ]);

        return $result;
    }

    // ---------------------------------------------------------
    //  SALES SIDE — customer ko jo invoices/payments/notes apply hote hain
    // ---------------------------------------------------------
    private function buildSalesSideEntries(string $id): array
    {
        $rows = [];

        $salesInvoices = SalesInvoice::where('party_id', $id)
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->get();

        // invoice_id (string) => invoice_type ('gst' | 'cash'), used to tag
        // payments/credit-notes that are linked to a specific invoice.
        $invoiceTypeMap = $salesInvoices->mapWithKeys(
            fn($inv) => [(string) $inv->_id => $inv->invoice_type ?? 'gst']
        )->toArray();

        foreach ($salesInvoices as $inv) {
            [$dueStatus, $dueDateStr] = $this->resolveInvoiceDueStatus($inv);

            $rows[] = [
                'raw_date'         => $inv->invoice_date,
                'date'             => $this->formatDate($inv->invoice_date),
                'voucher_type'     => 'Sale Invoice',
                'sr_no'            => $inv->invoice_number,
                'payment_mode'     => '—',
                'reference_number' => null,
                'debit'            => $this->toFloat($inv->grand_total),
                'credit'           => 0.0,
                'due_date'         => $dueDateStr,
                'due_status'       => $dueStatus,
                'invoice_type'     => $inv->invoice_type ?? 'gst',
                '_sort_priority'   => 1, // invoices first on a given date
            ];
        }

        foreach ($this->getSalesPaymentsIn($id) as $pmt) {
            $rows[] = [
                'raw_date'         => $pmt->payment_date,
                'date'             => $this->formatDate($pmt->payment_date),
                'voucher_type'     => 'Payment-in',
                'sr_no'            => $pmt->payment_number ?? '—',
                'payment_mode'     => $this->formatPaymentMode($pmt),
                'reference_number' => $pmt->reference_no ?? null,
                'debit'            => 0.0,
                'credit'           => $this->toFloat($pmt->amount),
                'invoice_type'     => $this->resolvePaymentInvoiceType($pmt, $invoiceTypeMap),
                '_sort_priority'   => 2,
            ];
        }

        foreach (CreditNote::where('party_id', $id)->get() as $cn) {
            $linkedInvId = (string) ($cn->sales_invoice_id ?? '');
            $rows[] = [
                'raw_date'         => $cn->credit_date,
                'date'             => $this->formatDate($cn->credit_date),
                'voucher_type'     => 'Credit Note',
                'sr_no'            => $cn->credit_note_number,
                'payment_mode'     => '—',
                'reference_number' => null,
                'debit'            => 0.0,
                'credit'           => $this->toFloat($cn->amount),
                // Credit note inherits its parent invoice's type; if it isn't
                // linked to a specific invoice, treat it as visible in both.
                'invoice_type'     => $invoiceTypeMap[$linkedInvId] ?? 'all',
                '_sort_priority'   => 3,
            ];
        }

        // Refund TO customer (credit note refunded via PurchasePayment with credit_refund subtype)
        $refunds = PurchasePayment::where('party_id', $id)
            ->where('payment_subtype', 'credit_refund')
            ->get();

        foreach ($refunds as $pmt) {
            $rows[] = [
                'raw_date'         => $pmt->payment_date,
                'date'             => $this->formatDate($pmt->payment_date),
                'voucher_type'     => 'Refund Payment',
                'sr_no'            => $pmt->payment_number ?? '—',
                'payment_mode'     => $this->formatPaymentMode($pmt),
                'reference_number' => $pmt->reference_no ?? null,
                'debit'            => $this->toFloat($pmt->amount),
                'credit'           => 0.0,
                'invoice_type'     => $this->resolvePaymentInvoiceType($pmt, $invoiceTypeMap),
                '_sort_priority'   => 4,
            ];
        }

        return $rows;
    }

    // =========================================================
    //  INVOICE-TYPE RESOLUTION FOR PAYMENTS
    //
    //  A payment's `allocations` array links it to one or more invoices.
    //  - If every linked invoice shares the same type (gst/cash) → tag with
    //    that type, so the GST/Cash filter can hide-or-show it correctly.
    //  - If it's linked to invoices of BOTH types, or to no invoice at all
    //    (e.g. a standalone advance / opening-balance payment) → tag as
    //    'all' so it stays visible regardless of which filter is active.
    //    This keeps the running balance correct under either filter instead
    //    of silently hiding money that did move.
    // =========================================================

    private function resolvePaymentInvoiceType($pmt, array $invoiceTypeMap): string
    {
        $allocations = $this->parseAllocations($pmt->allocations);
        $linkedTypes = [];

        foreach ($allocations as $alloc) {
            if (($alloc['type'] ?? '') === 'invoice' && !empty($alloc['invoice_id'])) {
                $invId = (string) $alloc['invoice_id'];
                if (isset($invoiceTypeMap[$invId])) {
                    $linkedTypes[$invoiceTypeMap[$invId]] = true;
                }
            }
        }

        // Fallback: direct invoice_id field on the payment (invoice-time payments)
        if (empty($linkedTypes)) {
            $directInvId = (string) ($pmt->sales_invoice_id ?? $pmt->purchase_invoice_id ?? '');
            if ($directInvId && isset($invoiceTypeMap[$directInvId])) {
                $linkedTypes[$invoiceTypeMap[$directInvId]] = true;
            }
        }

        if (empty($linkedTypes)) {
            return 'all'; // standalone payment, not tied to any specific invoice
        }

        if (count($linkedTypes) > 1) {
            return 'all'; // touches both gst and cash invoices — keep visible always
        }

        return array_key_first($linkedTypes);
    }

    // ---------------------------------------------------------
    //  PURCHASE SIDE — vendor ko jo invoices/payments/notes apply hote hain
    // ---------------------------------------------------------
    private function buildPurchaseSideEntries(string $id): array
    {
        $rows = [];

        $purchaseInvoices = PurchaseInvoice::where('party_id', $id)
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->get();

        // NOTE: falls back to 'gst' if PurchaseInvoice doesn't have an
        // invoice_type field — keeps these always visible under the GST
        // filter rather than silently disappearing.
        $invoiceTypeMap = $purchaseInvoices->mapWithKeys(
            fn($inv) => [(string) $inv->_id => $inv->invoice_type ?? 'gst']
        )->toArray();

        foreach ($purchaseInvoices as $inv) {
            [$dueStatus, $dueDateStr] = $this->resolveInvoiceDueStatus($inv);

            $rows[] = [
                'raw_date'         => $inv->invoice_date,
                'date'             => $this->formatDate($inv->invoice_date),
                'voucher_type'     => 'Purchase Invoice',
                'sr_no'            => $inv->invoice_number,
                'payment_mode'     => '—',
                'reference_number' => null,
                'debit'            => 0.0,
                'credit'           => $this->toFloat($inv->grand_total),
                'due_date'         => $dueDateStr,
                'due_status'       => $dueStatus,
                'invoice_type'     => $inv->invoice_type ?? 'gst',
                '_sort_priority'   => 1,
            ];
        }

        foreach ($this->getPurchasePaymentsOut($id) as $pmt) {
            $rows[] = [
                'raw_date'         => $pmt->payment_date,
                'date'             => $this->formatDate($pmt->payment_date),
                'voucher_type'     => 'Payment-out',
                'sr_no'            => $pmt->payment_number ?? '—',
                'payment_mode'     => $this->formatPaymentMode($pmt),
                'reference_number' => $pmt->reference_no ?? null,
                'debit'            => $this->toFloat($pmt->amount),
                'credit'           => 0.0,
                'invoice_type'     => $this->resolvePaymentInvoiceType($pmt, $invoiceTypeMap),
                '_sort_priority'   => 2,
            ];
        }

        foreach (DebitNote::where('party_id', $id)->get() as $dn) {
            $linkedInvId = (string) ($dn->purchase_invoice_id ?? '');
            $rows[] = [
                'raw_date'         => $dn->debit_date,
                'date'             => $this->formatDate($dn->debit_date),
                'voucher_type'     => 'Debit Note',
                'sr_no'            => $dn->debit_note_number,
                'payment_mode'     => '—',
                'reference_number' => null,
                'debit'            => $this->toFloat($dn->amount),
                'credit'           => 0.0,
                'invoice_type'     => $invoiceTypeMap[$linkedInvId] ?? 'all',
                '_sort_priority'   => 3,
            ];
        }

        // Refund FROM vendor (debit note refunded via SalesPayment with debit_refund subtype)
        $refunds = SalesPayment::where('party_id', $id)
            ->where('payment_subtype', 'debit_refund')
            ->get();

        foreach ($refunds as $pmt) {
            $rows[] = [
                'raw_date'         => $pmt->payment_date,
                'date'             => $this->formatDate($pmt->payment_date),
                'voucher_type'     => 'Refund Received',
                'sr_no'            => $pmt->payment_number ?? '—',
                'payment_mode'     => $this->formatPaymentMode($pmt),
                'reference_number' => $pmt->reference_no ?? null,
                'debit'            => 0.0,
                'credit'           => $this->toFloat($pmt->amount),
                'invoice_type'     => $this->resolvePaymentInvoiceType($pmt, $invoiceTypeMap),
                '_sort_priority'   => 4,
            ];
        }

        return $rows;
    }

    // =========================================================
    //  DUE STATUS  (Paid / Partially Paid / Unpaid + overdue days)
    // =========================================================

    private function resolveInvoiceDueStatus($inv): array
    {
        $balanceAmount = $this->toFloat($inv->balance_amount ?? 0);
        $dueDateRaw    = $inv->due_date ?? null;
        $dueDateStr    = $dueDateRaw ? $this->formatDate($dueDateRaw) : null;

        if ($balanceAmount <= 0.001) {
            return ['Paid', $dueDateStr];
        }

        $grandTotal = $this->toFloat($inv->grand_total ?? 0);
        $isPartial  = $grandTotal > 0 && $balanceAmount < $grandTotal;

        $overdueSuffix = '';
        if ($dueDateRaw) {
            try {
                $due   = Carbon::parse($this->sortableDate($dueDateRaw));
                $today = Carbon::today();
                if ($today->gt($due)) {
                    $overdueSuffix = ' (' . $today->diffInDays($due) . ')';
                }
            } catch (\Exception $e) {
                // leave suffix blank if date parsing fails
            }
        }

        $label = $isPartial ? 'Partially Paid' : 'Unpaid';

        return [$label . $overdueSuffix, $dueDateStr];
    }

    // =========================================================
    //  PAYMENT MODE FORMATTER  ->  "Upi (TXN12345)" / "Cash"
    //
    //  NOTE: actual DB column is `payment_method` (not `payment_mode`),
    //  and reference column is `reference_no` (not `reference_number`).
    //  Uses the model's payment_method_text accessor when available so
    //  labels stay in sync with SalesPayment::getPaymentMethodTextAttribute().
    // =========================================================

    private function formatPaymentMode($pmt): string
    {
        if (!empty($pmt->payment_method_text)) {
            return $pmt->payment_method_text;
        }

        $method = $pmt->payment_method ?? null;
        return $method ? ucfirst($method) : '—';
    }

    // =========================================================
    //  PAYMENT QUERIES
    // =========================================================

    private function getSalesPaymentsIn(string $partyId): \Illuminate\Support\Collection
    {
        return SalesPayment::where('party_id', $partyId)
            ->where(function ($q) {
                $q->where('payment_type', 'payment_in')
                  ->orWhereNull('payment_type')
                  ->orWhere('payment_type', '');
            })
            ->where(function ($q) {
                $q->where('payment_subtype', '!=', 'debit_refund')
                  ->orWhereNull('payment_subtype');
            })
            ->orderBy('payment_date')
            ->get();
    }

    private function getPurchasePaymentsOut(string $partyId): \Illuminate\Support\Collection
    {
        return PurchasePayment::where('party_id', $partyId)
            ->where(function ($q) {
                $q->where('payment_type', 'payment_out')
                  ->orWhereNull('payment_type')
                  ->orWhere('payment_type', '');
            })
            ->where(function ($q) {
                $q->where('payment_subtype', '!=', 'credit_refund')
                  ->orWhereNull('payment_subtype');
            })
            ->orderBy('payment_date')
            ->get();
    }

    // =========================================================
    //  PROFILE TAB
    // =========================================================

    private function buildProfile(string $partyType, $party): array
    {
        $billingType  = 'billing';
        $shippingType = 'shipping';

        $billing  = $party->addresses->where('type', $billingType)->where('is_default', true)->first()
                 ?? $party->addresses->where('type', $billingType)->first();
        $shipping = $party->addresses->where('type', $shippingType)->where('is_default', true)->first()
                 ?? $party->addresses->where('type', $shippingType)->first();

        if ($partyType === 'vendor') {
            return [
                'name'             => $party->company_name,
                'contact_person'   => $party->name,
                'phone'            => $party->phone,
                'email'            => $party->email,
                'gst_number'       => $party->gst_number,
                'pan_number'       => $party->pan_number ?? null,
                'opening_balance'  => $this->toFloat($party->opening_balance),
                'credit_limit'     => $this->toFloat($party->credit_limit),
                'bank_name'        => $party->bank_name,
                'account_number'   => $party->account_number,
                'ifsc_code'        => $party->ifsc_code,
                'billing_address'  => $billing  ? $this->formatAddress($billing)  : null,
                'shipping_address' => $shipping ? $this->formatAddress($shipping) : null,
                'status'           => $party->status,
                'party_type'       => 'Vendor',
                'notes'            => $party->notes,
            ];
        }

        return [
            'name'             => $party->name,
            'contact_person'   => null,
            'phone'            => $party->phone,
            'email'            => $party->email,
            'gst_number'       => $party->gst_number,
            'pan_number'       => $party->pan_number ?? null,
            'opening_balance'  => $this->toFloat($party->opening_balance),
            'credit_limit'     => $this->toFloat($party->credit_limit),
            'bank_name'        => null,
            'account_number'   => null,
            'ifsc_code'        => null,
            'billing_address'  => $billing  ? $this->formatAddress($billing)  : null,
            'shipping_address' => $shipping ? $this->formatAddress($shipping) : null,
            'status'           => $party->status,
            'party_type'       => ucfirst($party->party_type),
            'notes'            => $party->notes,
        ];
    }

    private function formatAddress($addr): string
    {
        return implode(', ', array_filter([
            $addr->address, $addr->city, $addr->state, $addr->pincode, $addr->country,
        ]));
    }

    // =========================================================
    //  ITEM WISE REPORT TAB
    // =========================================================

    private function buildItemReport(string $partyType, string $id): \Illuminate\Support\Collection
    {
        $productMap = [];

        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {
            foreach (SalesInvoice::where('party_id', $id)->where('status', '!=', 'draft')->with('items')->get() as $inv) {
                foreach ($inv->items as $item) {
                    $key  = $item->sku ?: ((string)($item->product_id ?? '') . '_' . ((string)($item->variant_id ?? '')));
                    $name = $item->variant_name ? $item->product_name . ' - ' . $item->variant_name : $item->product_name;

                    $productMap[$key] ??= [
                        'product_name' => $name, 'sku' => $item->sku ?? '—', 'hsn' => $item->hsn_sac ?? '—',
                        'sale_qty' => 0.0, 'sale_amount' => 0.0, 'purchase_qty' => 0.0, 'purchase_amount' => 0.0,
                    ];

                    $productMap[$key]['sale_qty']    += (float) $item->quantity;
                    $productMap[$key]['sale_amount'] += $this->toFloat($item->total);
                }
            }
        }

        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {
            foreach (PurchaseInvoice::where('party_id', $id)->where('status', '!=', 'draft')->with('items')->get() as $inv) {
                foreach ($inv->items as $item) {
                    $key = $item->sku ?: ((string)($item->product_id ?? '') . '_' . ((string)($item->variant_id ?? '')));

                    $productMap[$key] ??= [
                        'product_name' => $item->product_name, 'sku' => $item->sku ?? '—', 'hsn' => $item->hsn_sac ?? '—',
                        'sale_qty' => 0.0, 'sale_amount' => 0.0, 'purchase_qty' => 0.0, 'purchase_amount' => 0.0,
                    ];

                    $productMap[$key]['purchase_qty']    += (float) $item->quantity;
                    $productMap[$key]['purchase_amount'] += $this->toFloat($item->total);
                }
            }
        }

        return collect(array_values($productMap))
            ->sortByDesc(fn($r) => $r['sale_amount'] + $r['purchase_amount'])
            ->values();
    }

    // =========================================================
    //  SUMMARY  (matches MyBillBook header block)
    //
    //  total_sales       -> sum of all Sale/Purchase Invoice debit/credit
    //  total_received    -> sum of all Payment-in / Payment-out credit/debit
    //  total_receivable  -> closing balance (what party currently owes/owed)
    //  overdue_amount    -> sum of balance_amount on invoices past due_date
    // =========================================================

    private function buildSummary(\Illuminate\Support\Collection $ledger, string $partyType): array
    {
        $movementRows = $ledger->where('is_opening', false)->where('is_closing', false);

        // "Sales" side = Sale Invoice rows, "Purchase" side = Purchase Invoice rows.
        // For dealer/distributor (both sides active) we add both together.
        $totalSales = $movementRows
            ->whereIn('voucher_type', ['Sale Invoice', 'Purchase Invoice'])
            ->sum(fn($r) => $r['debit'] + $r['credit']);

        $totalReceived = $movementRows
            ->whereIn('voucher_type', ['Payment-in', 'Payment-out'])
            ->sum(fn($r) => $r['debit'] + $r['credit']);

        // Overdue = sum of unpaid/partially-paid invoice rows whose due date has passed.
        // due_status carries "Unpaid (N)" / "Partially Paid (N)" once overdue, plain
        // "Unpaid" / "Partially Paid" if not yet overdue, or "Paid" / null otherwise.
        $overdueAmount = $movementRows
            ->filter(function ($r) {
                $status = (string) ($r['due_status'] ?? '');
                return preg_match('/^(Unpaid|Partially Paid).*\(\d+\)$/', $status) === 1;
            })
            ->sum(fn($r) => $r['debit'] - $r['credit']);

        return [
            'total_debit'      => round($movementRows->sum('debit'), 2),
            'total_credit'     => round($movementRows->sum('credit'), 2),
            'opening_balance'  => round($ledger->first()['balance'] ?? 0, 2),
            'closing_balance'  => round($ledger->last()['balance'] ?? 0, 2),
            'total_sales'      => round($totalSales, 2),
            'total_received'   => round($totalReceived, 2),
            'overdue_amount'   => round($overdueAmount, 2),
        ];
    }

    // =========================================================
    //  GENERIC HELPERS
    // =========================================================

    // Kept for future use (e.g. if you later want to show which invoice a
    // payment's allocations point to in a tooltip). Not used in the current
    // flat single-row-per-voucher ledger.
    private function parseAllocations($allocations): array
    {
        if (is_string($allocations)) return json_decode($allocations, true) ?? [];
        return is_array($allocations) ? $allocations : [];
    }

    private function sortableDate($date): string
    {
        if (!$date) return '0000-00-00';
        if ($date instanceof \Carbon\Carbon)            return $date->format('Y-m-d H:i:s');
        if ($date instanceof \MongoDB\BSON\UTCDateTime) return $date->toDateTime()->format('Y-m-d H:i:s');
        try { return Carbon::parse($date)->format('Y-m-d H:i:s'); }
        catch (\Exception $e) { return (string) $date; }
    }

    private function toFloat($value): float
    {
        if ($value instanceof Decimal128) return (float) $value->__toString();
        return (float) $value;
    }

    private function formatDate($date): string
    {
        if (!$date) return '—';
        if ($date instanceof \Carbon\Carbon)            return $date->format('d M Y');
        if ($date instanceof \MongoDB\BSON\UTCDateTime) return $date->toDateTime()->format('d M Y');
        try { return Carbon::parse($date)->format('d M Y'); }
        catch (\Exception $e) { return (string) $date; }
    }
}