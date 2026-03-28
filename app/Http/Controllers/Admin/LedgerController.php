<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Vendor;
use App\Models\VendorAddress;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchasePayment;
use App\Models\CreditNote;
use App\Models\DebitNote;
use Illuminate\Http\Request;
use MongoDB\BSON\Decimal128;

class LedgerController extends Controller
{
    // =========================================================
    //  ENTRY POINT — route: admin.ledger.show
    //  GET /admin/ledger/{partyType}/{id}
    //  partyType: customer | dealer | distributor | vendor
    // =========================================================

    public function show(Request $request, string $partyType, string $id)
    {
        // 1. Load Party -------------------------------------------------
        if ($partyType === 'vendor') {
            $party = Vendor::with('addresses')->findOrFail($id);
        } else {
            $party = Customer::with('addresses')->findOrFail($id);
        }

        // 2. Build all 4 tab datasets ------------------------------------
        $transactions  = $this->buildTransactions($partyType, $id);
        $ledger        = $this->buildLedger($partyType, $id, $party);
        $itemReport    = $this->buildItemReport($partyType, $id);
        $profile       = $this->buildProfile($partyType, $party);

        // 3. Summary stats for header cards
        $summary = $this->buildSummary($ledger, $partyType);

        return view('admin.ledger.show', compact(
            'party', 'partyType',
            'transactions', 'ledger',
            'itemReport', 'profile',
            'summary'
        ));
    }

    // =========================================================
    //  TAB 1 — TRANSACTIONS
    // =========================================================

    private function buildTransactions(string $partyType, string $id): \Illuminate\Support\Collection
    {
        $txns = collect();

        // ---- Sales side (customer, dealer, distributor) ----------------
        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {

            // Sales Invoices
            $salesInvoices = SalesInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->orderBy('invoice_date')
                ->get();

            foreach ($salesInvoices as $inv) {
                $txns->push([
                    'date'        => $this->formatDate($inv->invoice_date),
                    'raw_date'    => $inv->invoice_date,
                    'type'        => 'Sale Invoice',
                    'type_badge'  => 'sale',
                    'number'      => $inv->invoice_number,
                    'amount'      => $this->toFloat($inv->grand_total),
                    'amount_type' => 'debit',
                    'status'      => $inv->payment_status ?? $inv->status,
                ]);
            }

            // Payments In
            $paymentsIn = SalesPayment::where('party_id', $id)
                ->where('payment_type', 'payment_in')
                ->orderBy('payment_date')
                ->get();

            foreach ($paymentsIn as $pmt) {
                $txns->push([
                    'date'        => $this->formatDate($pmt->payment_date),
                    'raw_date'    => $pmt->payment_date,
                    'type'        => $pmt->payment_subtype === 'sales_payment' ? 'Payment In' : 'Debit Refund',
                    'type_badge'  => 'payment_in',
                    'number'      => $pmt->payment_number ?? ('PMT-' . substr((string)$pmt->_id, -6)),
                    'amount'      => $this->toFloat($pmt->amount),
                    'amount_type' => 'credit',
                    'status'      => $pmt->status,
                ]);
            }

            // Credit Notes (Sales Return)
            $creditNotes = CreditNote::where('party_id', $id)
                ->orderBy('credit_date')
                ->get();

            foreach ($creditNotes as $cn) {
                $txns->push([
                    'date'        => $this->formatDate($cn->credit_date),
                    'raw_date'    => $cn->credit_date,
                    'type'        => 'Credit Note',
                    'type_badge'  => 'credit_note',
                    'number'      => $cn->credit_note_number,
                    'amount'      => $this->toFloat($cn->amount),
                    'amount_type' => 'credit',
                    'status'      => $cn->status,
                ]);
            }
        }

        // ---- Purchase side (vendor, dealer, distributor) ---------------
        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {

            // Purchase Invoices
            $purchaseInvoices = PurchaseInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->orderBy('invoice_date')
                ->get();

            foreach ($purchaseInvoices as $inv) {
                $txns->push([
                    'date'        => $this->formatDate($inv->invoice_date),
                    'raw_date'    => $inv->invoice_date,
                    'type'        => 'Purchase Invoice',
                    'type_badge'  => 'purchase',
                    'number'      => $inv->invoice_number,
                    'amount'      => $this->toFloat($inv->grand_total),
                    'amount_type' => 'credit',
                    'status'      => $inv->payment_status ?? $inv->status,
                ]);
            }

            // Payments Out
            $paymentsOut = PurchasePayment::where('party_id', $id)
                ->where('payment_type', 'payment_out')
                ->orderBy('payment_date')
                ->get();

            foreach ($paymentsOut as $pmt) {
                $txns->push([
                    'date'        => $this->formatDate($pmt->payment_date),
                    'raw_date'    => $pmt->payment_date,
                    'type'        => $pmt->payment_subtype === 'credit_refund' ? 'Credit Refund' : 'Payment Out',
                    'type_badge'  => 'payment_out',
                    'number'      => $pmt->payment_number ?? ('PO-' . substr((string)$pmt->_id, -6)),
                    'amount'      => $this->toFloat($pmt->amount),
                    'amount_type' => 'debit',
                    'status'      => $pmt->status,
                ]);
            }
            // Refund Received (Payment IN from vendor)
$refunds = SalesPayment::where('party_id', $id)
    ->where('payment_type', 'payment_in')
    ->get();

foreach ($refunds as $pmt) {

    $allocations = is_string($pmt->allocations)
    ? json_decode($pmt->allocations, true)
    : $pmt->allocations;

    if (!$allocations) continue;

    foreach ($allocations as $alloc) {
        if ($alloc['type'] === 'debit_note') {

            $txns->push([
                'date'        => $this->formatDate($pmt->payment_date),
                'raw_date'    => $pmt->payment_date,
                'type'        => 'Debit Refund',
                'type_badge'  => 'payment_in',
                'number'      => $pmt->payment_number,
                'amount'      => $this->toFloat($pmt->amount),
                'amount_type' => 'debit',
                'status'      => $pmt->status,
            ]);
        }
    }
}

            // Debit Notes (Purchase Return)
            $debitNotes = DebitNote::where('party_id', $id)
                ->orderBy('debit_date')
                ->get();

            foreach ($debitNotes as $dn) {
                $txns->push([
                    'date'        => $this->formatDate($dn->debit_date),
                    'raw_date'    => $dn->debit_date,
                    'type'        => 'Debit Note',
                    'type_badge'  => 'debit_note',
                    'number'      => $dn->debit_note_number,
                    'amount'      => $this->toFloat($dn->amount),
                    'amount_type' => 'debit',
                    'status'      => $dn->status,
                ]);
            }
        }

        return $txns->sortBy('raw_date')->values();
    }

    // =========================================================
    //  TAB 2 — PROFILE
    // =========================================================

    private function buildProfile(string $partyType, $party): array
    {
        if ($partyType === 'vendor') {
            $billing  = $party->addresses->where('type', 'billing')->where('is_default', true)->first()
                     ?? $party->addresses->where('type', 'billing')->first();
            $shipping = $party->addresses->where('type', 'shipping')->where('is_default', true)->first()
                     ?? $party->addresses->where('type', 'shipping')->first();

            return [
                'name'            => $party->company_name,
                'contact_person'  => $party->name,
                'phone'           => $party->phone,
                'email'           => $party->email,
                'gst_number'      => $party->gst_number,
                'pan_number'      => $party->pan_number ?? null,
                'opening_balance' => $this->toFloat($party->opening_balance),
                'credit_limit'    => $this->toFloat($party->credit_limit),
                'bank_name'       => $party->bank_name,
                'account_number'  => $party->account_number,
                'ifsc_code'       => $party->ifsc_code,
                'billing_address' => $billing ? $this->formatAddress($billing) : null,
                'shipping_address'=> $shipping ? $this->formatAddress($shipping) : null,
                'status'          => $party->status,
                'party_type'      => 'Vendor',
                'notes'           => $party->notes,
            ];
        } else {
            $billing  = $party->addresses->where('type', 'billing')->where('is_default', true)->first()
                     ?? $party->addresses->where('type', 'billing')->first();
            $shipping = $party->addresses->where('type', 'shipping')->where('is_default', true)->first()
                     ?? $party->addresses->where('type', 'shipping')->first();

            return [
                'name'            => $party->name,
                'contact_person'  => null,
                'phone'           => $party->phone,
                'email'           => $party->email,
                'gst_number'      => $party->gst_number,
                'pan_number'      => $party->pan_number ?? null,
                'opening_balance' => $this->toFloat($party->opening_balance),
                'credit_limit'    => $this->toFloat($party->credit_limit),
                'bank_name'       => null,
                'account_number'  => null,
                'ifsc_code'       => null,
                'billing_address' => $billing ? $this->formatAddress($billing) : null,
                'shipping_address'=> $shipping ? $this->formatAddress($shipping) : null,
                'status'          => $party->status,
                'party_type'      => ucfirst($party->party_type),
                'notes'           => $party->notes,
            ];
        }
    }

    private function formatAddress($addr): string
    {
        $parts = array_filter([
            $addr->address,
            $addr->city,
            $addr->state,
            $addr->pincode,
            $addr->country,
        ]);
        return implode(', ', $parts);
    }

    // =========================================================
    //  TAB 3 — LEDGER STATEMENT
    // =========================================================

    private function buildLedger(string $partyType, string $id, $party): \Illuminate\Support\Collection
    {
        $entries = collect();
        $openingBalance = $this->toFloat($party->opening_balance ?? 0);

        // Opening Balance Entry
        $entries->push([
            'date'         => null,
            'raw_date'     => null,
            'voucher_type' => 'Opening Balance',
            'voucher_no'   => '—',
            'debit'        => 0.0,
            'credit'       => 0.0,
            'tds_by_party' => 0.0,
            'tds_by_self'  => 0.0,
            'balance'      => $openingBalance,
            'is_opening'   => true,
        ]);

        // ---- SALES SIDE (customer / dealer / distributor) -----
        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {

            // Sale Invoices → Debit
            $salesInvoices = SalesInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->get();

            foreach ($salesInvoices as $inv) {
                $entries->push([
                    'date'         => $this->formatDate($inv->invoice_date),
                    'raw_date'     => $inv->invoice_date,
                    'voucher_type' => 'Sale Invoice',
                    'voucher_no'   => $inv->invoice_number,
                    'debit'        => $this->toFloat($inv->grand_total),
                    'credit'       => 0.0,
                    'tds_by_party' => 0.0,
                    'tds_by_self'  => 0.0,
                    'balance'      => 0.0,
                    'is_opening'   => false,
                ]);
            }

            // Payment In → Credit
            $paymentsIn = SalesPayment::where('party_id', $id)
                ->where('payment_type', 'payment_in')
                ->get();

            foreach ($paymentsIn as $pmt) {
                $tdsParty = $this->extractTDS($pmt->allocations ?? [], 'tds_by_party');
                $tdsSelf  = $this->extractTDS($pmt->allocations ?? [], 'tds_by_self');
                $entries->push([
                    'date'         => $this->formatDate($pmt->payment_date),
                    'raw_date'     => $pmt->payment_date,
                    'voucher_type' => $pmt->payment_subtype === 'sales_payment' ? 'Payment In' : 'Debit Refund Recv.',
                    'voucher_no'   => $pmt->payment_number ?? ('PMT-' . substr((string)$pmt->_id, -6)),
                    'debit'        => 0.0,
                    'credit'       => $this->toFloat($pmt->amount),
                    'tds_by_party' => $tdsParty,
                    'tds_by_self'  => $tdsSelf,
                    'balance'      => 0.0,
                    'is_opening'   => false,
                ]);
            }

            // Credit Note → Credit
            $creditNotes = CreditNote::where('party_id', $id)->get();
            foreach ($creditNotes as $cn) {
                $entries->push([
                    'date'         => $this->formatDate($cn->credit_date),
                    'raw_date'     => $cn->credit_date,
                    'voucher_type' => 'Credit Note',
                    'voucher_no'   => $cn->credit_note_number,
                    'debit'        => 0.0,
                    'credit'       => $this->toFloat($cn->amount),
                    'tds_by_party' => 0.0,
                    'tds_by_self'  => 0.0,
                    'balance'      => 0.0,
                    'is_opening'   => false,
                ]);
            }
        }

        // ---- PURCHASE SIDE (vendor / dealer / distributor) ----
        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {

            // Purchase Invoice → Credit (we owe them)
            $purchaseInvoices = PurchaseInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->get();

            foreach ($purchaseInvoices as $inv) {
                $entries->push([
                    'date'         => $this->formatDate($inv->invoice_date),
                    'raw_date'     => $inv->invoice_date,
                    'voucher_type' => 'Purchase Invoice',
                    'voucher_no'   => $inv->invoice_number,
                    'debit'        => 0.0,
                    'credit'       => $this->toFloat($inv->grand_total),
                    'tds_by_party' => 0.0,
                    'tds_by_self'  => 0.0,
                    'balance'      => 0.0,
                    'is_opening'   => false,
                ]);
            }

            // Payment Out → Debit (we paid them)
            $paymentsOut = PurchasePayment::where('party_id', $id)
                ->where('payment_type', 'payment_out')
                ->get();

            foreach ($paymentsOut as $pmt) {
                $tdsParty = $this->extractTDS($pmt->allocations ?? [], 'tds_by_party');
                $tdsSelf  = $this->extractTDS($pmt->allocations ?? [], 'tds_by_self');
                $entries->push([
                    'date'         => $this->formatDate($pmt->payment_date),
                    'raw_date'     => $pmt->payment_date,
                    'voucher_type' => $pmt->payment_subtype === 'credit_refund' ? 'Credit Refund Out' : 'Payment Out',
                    'voucher_no'   => $pmt->payment_number ?? ('PO-' . substr((string)$pmt->_id, -6)),
                    'debit'        => $this->toFloat($pmt->amount),
                    'credit'       => 0.0,
                    'tds_by_party' => $tdsParty,
                    'tds_by_self'  => $tdsSelf,
                    'balance'      => 0.0,
                    'is_opening'   => false,
                ]);
            }
            // Refund Received from Vendor (Payment IN)
            $refunds = SalesPayment::where('party_id', $id)
                ->where('payment_type', 'payment_in')
                ->get();

            foreach ($refunds as $pmt) {

                $allocations = is_string($pmt->allocations)
    ? json_decode($pmt->allocations, true)
    : $pmt->allocations;

                if (!$allocations) continue;

                foreach ($allocations as $alloc) {
                    if ($alloc['type'] === 'debit_note') {

                        $entries->push([
                            'date'         => $this->formatDate($pmt->payment_date),
                            'raw_date'     => $pmt->payment_date,
                            'voucher_type' => 'Debit Note Refund',
                            'voucher_no'   => $pmt->payment_number,
                            'debit'        => $this->toFloat($pmt->amount),
                            'credit'       => 0.0,
                            'tds_by_party' => 0.0,
                            'tds_by_self'  => 0.0,
                            'balance'      => 0.0,
                            'is_opening'   => false,
                        ]);
                    }
                }
            }
            // Debit Note → Debit (they owe us from return)
            $debitNotes = DebitNote::where('party_id', $id)->get();
            foreach ($debitNotes as $dn) {
                $entries->push([
                    'date'         => $this->formatDate($dn->debit_date),
                    'raw_date'     => $dn->debit_date,
                    'voucher_type' => 'Debit Note',
                    'voucher_no'   => $dn->debit_note_number,
                    'debit'        => $this->toFloat($dn->amount),
                    'credit'       => 0.0,
                    'tds_by_party' => 0.0,
                    'tds_by_self'  => 0.0,
                    'balance'      => 0.0,
                    'is_opening'   => false,
                ]);
            }
        }

        // Sort non-opening entries by date ASC, then compute running balance
        $opening = $entries->first();
        $rest = $entries->slice(1)->sortBy(function ($e) {
            if (!$e['raw_date']) return '0000-00-00';
            $d = $e['raw_date'];
            if ($d instanceof \Carbon\Carbon) return $d->format('Y-m-d H:i:s');
            if ($d instanceof \MongoDB\BSON\UTCDateTime) return $d->toDateTime()->format('Y-m-d H:i:s');
            return (string)$d;
        })->values();

        $balance = $opening['balance'];
        $result  = collect([$opening]);

        foreach ($rest as $entry) {
            $balance = $balance + $entry['debit'] - $entry['credit'];
            $entry['balance'] = round($balance, 2);
            $result->push($entry);
        }

        return $result;
    }

    private function extractTDS(array $allocations, string $key): float
    {
        return collect($allocations)
            ->filter(fn($a) => isset($a['type']) && $a['type'] === $key)
            ->sum('amount');
    }

    // =========================================================
    //  TAB 4 — ITEM WISE REPORT
    // =========================================================

    private function buildItemReport(string $partyType, string $id): \Illuminate\Support\Collection
    {
        $productMap = [];

        // Sales items
        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {
            $salesInvoices = SalesInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->with('items')
                ->get();

            foreach ($salesInvoices as $inv) {
                foreach ($inv->items as $item) {
                    $key  = $item->sku ?: ((string)($item->product_id ?? '') . '_' . ((string)($item->variant_id ?? '')));
                    $name = $item->variant_name ? $item->product_name . ' - ' . $item->variant_name : $item->product_name;

                    if (!isset($productMap[$key])) {
                        $productMap[$key] = [
                            'product_name'    => $name,
                            'sku'             => $item->sku ?? '—',
                            'hsn'             => $item->hsn_sac ?? '—',
                            'sale_qty'        => 0.0,
                            'sale_amount'     => 0.0,
                            'purchase_qty'    => 0.0,
                            'purchase_amount' => 0.0,
                        ];
                    }
                    $productMap[$key]['sale_qty']    += (float)$item->quantity;
                    $productMap[$key]['sale_amount'] += $this->toFloat($item->total);
                }
            }
        }

        // Purchase items
        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {
            $purchaseInvoices = PurchaseInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->with('items')
                ->get();

            foreach ($purchaseInvoices as $inv) {
                foreach ($inv->items as $item) {
                    $key  = $item->sku ?: ((string)($item->product_id ?? '') . '_' . ((string)($item->variant_id ?? '')));
                    $name = $item->product_name;

                    if (!isset($productMap[$key])) {
                        $productMap[$key] = [
                            'product_name'    => $name,
                            'sku'             => $item->sku ?? '—',
                            'hsn'             => $item->hsn_sac ?? '—',
                            'sale_qty'        => 0.0,
                            'sale_amount'     => 0.0,
                            'purchase_qty'    => 0.0,
                            'purchase_amount' => 0.0,
                        ];
                    }
                    $productMap[$key]['purchase_qty']    += (float)$item->quantity;
                    $productMap[$key]['purchase_amount'] += $this->toFloat($item->total);
                }
            }
        }

        return collect(array_values($productMap))
            ->sortByDesc(fn($r) => $r['sale_amount'] + $r['purchase_amount'])
            ->values();
    }

    // =========================================================
    //  SUMMARY CARDS
    // =========================================================

    private function buildSummary(\Illuminate\Support\Collection $ledger, string $partyType): array
    {
        $totalDebit  = round($ledger->where('is_opening', false)->sum('debit'), 2);
        $totalCredit = round($ledger->where('is_opening', false)->sum('credit'), 2);
        $closing     = $ledger->last()['balance'] ?? 0;
        $opening     = $ledger->first()['balance'] ?? 0;

        return [
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'closing_balance' => round($closing, 2),
            'opening_balance' => round($opening, 2),
        ];
    }

    // =========================================================
    //  HELPERS
    // =========================================================

    private function toFloat($value): float
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    private function formatDate($date): string
    {
        if (!$date) return '—';
        if ($date instanceof \Carbon\Carbon) return $date->format('d-m-Y');
        if ($date instanceof \MongoDB\BSON\UTCDateTime) return $date->toDateTime()->format('d-m-Y');
        try {
            return \Carbon\Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return (string)$date;
        }
    }
}
