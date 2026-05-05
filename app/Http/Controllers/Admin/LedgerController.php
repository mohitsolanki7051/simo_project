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
use MongoDB\BSON\Decimal128;

class LedgerController extends Controller
{
    // =========================================================
    //  ENTRY POINT
    // =========================================================

    public function show(Request $request, string $partyType, string $id)
    {
        if ($partyType === 'vendor') {
            $party = Vendor::with('addresses')->findOrFail($id);
        } else {
            $party = Customer::with('addresses')->findOrFail($id);
        }

        $transactions = $this->buildTransactions($partyType, $id);
        $ledger       = $this->buildLedger($partyType, $id, $party);
        $itemReport   = $this->buildItemReport($partyType, $id);
        $profile      = $this->buildProfile($partyType, $party);
        $summary      = $this->buildSummary($ledger);

        return view('admin.ledger.show', compact(
            'party', 'partyType',
            'transactions', 'ledger',
            'itemReport', 'profile',
            'summary'
        ));
    }

    // =========================================================
    //  HELPER: Payment number resolve karo
    //
    //  - Agar payment_number hai → woh dikhao (SalesPaymentController se)
    //  - Agar nahi hai (invoice-time payment) → invoice number dikhao
    //  - invoiceNumberMap: [ invoice_id => invoice_number ]
    // =========================================================

    private function resolvePaymentVoucherNumber($pmt, array $invoiceNumberMap, string $invoiceField): string
    {
        // Has its own payment number — use it
        if (!empty($pmt->payment_number)) {
            return $pmt->payment_number;
        }

        // Invoice-time payment — find invoice number from allocations first
        $allocations = $this->parseAllocations($pmt->allocations);
        foreach ($allocations as $alloc) {
            if (($alloc['type'] ?? '') === 'invoice' && !empty($alloc['invoice_id'])) {
                $invId = (string) $alloc['invoice_id'];
                if (!empty($invoiceNumberMap[$invId])) {
                    return $invoiceNumberMap[$invId];
                }
            }
            // Also check invoice_number stored directly in allocation
            if (($alloc['type'] ?? '') === 'invoice' && !empty($alloc['invoice_number'])) {
                return $alloc['invoice_number'];
            }
        }

        // Fallback: direct invoice_id field on payment record
        $directInvId = (string)($pmt->{$invoiceField} ?? '');
        if ($directInvId && !empty($invoiceNumberMap[$directInvId])) {
            return $invoiceNumberMap[$directInvId];
        }

        // Last resort fallback (should never reach here after fixes)
        return '—';
    }

    // =========================================================
    //  HELPER: Get all SalesPayments for a party
    //  Handles both old records (no payment_type) and new records
    //  Excludes debit_refund subtype (those are vendor-side)
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
            ->get();
    }

    // =========================================================
    //  HELPER: Get all PurchasePayments for a party
    //  Excludes credit_refund subtype
    // =========================================================

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
            ->get();
    }

    // =========================================================
    //  HELPER: Find which invoice a payment belongs to
    //  Returns: [ invoice_id => amount, ... ]
    // =========================================================

    private function resolvePaymentInvoiceMap($pmt, string $invoiceField): array
    {
        $allocations      = $this->parseAllocations($pmt->allocations);
        $linkedInvoiceIds = [];

        foreach ($allocations as $alloc) {
            if (($alloc['type'] ?? '') === 'invoice' && !empty($alloc['invoice_id'])) {
                $linkedInvoiceIds[(string)$alloc['invoice_id']] =
                    $this->toFloat($alloc['amount'] ?? $pmt->amount);
            }
        }

        // Fallback: direct invoice_id field
        if (empty($linkedInvoiceIds)) {
            $directId = (string)($pmt->{$invoiceField} ?? '');
            if ($directId) {
                $linkedInvoiceIds[$directId] = $this->toFloat($pmt->amount);
            }
        }

        return $linkedInvoiceIds;
    }

    // =========================================================
    //  TAB 1 — TRANSACTIONS
    // =========================================================

    private function buildTransactions(string $partyType, string $id): \Illuminate\Support\Collection
    {
        $rows = collect();

        // ── SALES SIDE ──────────────────────────────────────────────
        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {

            $salesInvoices = SalesInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->orderBy('invoice_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Build invoice number map for quick lookup
            $invoiceNumberMap = $salesInvoices->pluck('invoice_number', '_id')
                ->mapWithKeys(fn($num, $id) => [(string)$id => $num])
                ->toArray();

            $allPaymentsIn  = $this->getSalesPaymentsIn($id);
            $allCreditNotes = CreditNote::where('party_id', $id)->get();

            $paymentsByInvoice  = [];
            $standalonePayments = [];

            foreach ($allPaymentsIn as $pmt) {
                $map = $this->resolvePaymentInvoiceMap($pmt, 'sales_invoice_id');

                // Voucher number: payment_number if exists, else invoice number
                $voucherNo = $this->resolvePaymentVoucherNumber($pmt, $invoiceNumberMap, 'sales_invoice_id');

                if (!empty($map)) {
                    foreach ($map as $invId => $amount) {
                        $paymentsByInvoice[$invId][] = [
                            'raw_date'    => $pmt->payment_date,
                            'date'        => $this->formatDate($pmt->payment_date),
                            'type'        => 'Payment In',
                            'type_badge'  => 'payment_in',
                            'number'      => $voucherNo,
                            'amount'      => $amount,
                            'amount_type' => 'credit',
                            'status'      => $pmt->status ?? 'completed',
                            'is_child'    => true,
                            'is_parent'   => false,
                        ];
                    }
                } else {
                    $standalonePayments[] = [
                        'raw_date'    => $pmt->payment_date,
                        'date'        => $this->formatDate($pmt->payment_date),
                        'type'        => 'Payment In',
                        'type_badge'  => 'payment_in',
                        'number'      => $voucherNo,
                        'amount'      => $this->toFloat($pmt->amount),
                        'amount_type' => 'credit',
                        'status'      => $pmt->status ?? 'completed',
                        'is_child'    => false,
                        'is_parent'   => false,
                    ];
                }
            }

            // Credit notes by invoice
            $cnByInvoice = [];
            foreach ($allCreditNotes as $cn) {
                $invId = (string)($cn->sales_invoice_id ?? '');
                if ($invId) {
                    $cnByInvoice[$invId][] = [
                        'raw_date'    => $cn->credit_date,
                        'date'        => $this->formatDate($cn->credit_date),
                        'type'        => 'Credit Note',
                        'type_badge'  => 'credit_note',
                        'number'      => $cn->credit_note_number,
                        'amount'      => $this->toFloat($cn->amount),
                        'amount_type' => 'credit',
                        'status'      => $cn->status,
                        'is_child'    => true,
                        'is_parent'   => false,
                    ];
                }
            }

            // Build flat rows: latest invoice first, children date ASC below it
            foreach ($salesInvoices as $inv) {
                $invId = (string) $inv->_id;

                $rows->push([
                    'date'        => $this->formatDate($inv->invoice_date),
                    'raw_date'    => $inv->invoice_date,
                    'type'        => 'Sale Invoice',
                    'type_badge'  => 'sale',
                    'number'      => $inv->invoice_number,
                    'amount'      => $this->toFloat($inv->grand_total),
                    'amount_type' => 'debit',
                    'status'      => $inv->payment_status ?? $inv->status,
                    'is_child'    => false,
                    'is_parent'   => true,
                    'balance'     => $this->toFloat($inv->balance_amount),
                ]);

                $children = collect();
                foreach ($paymentsByInvoice[$invId] ?? [] as $c) { $children->push($c); }
                foreach ($cnByInvoice[$invId]        ?? [] as $c) { $children->push($c); }

                foreach ($children->sortBy(fn($c) => $this->sortableDate($c['raw_date'])) as $child) {
                    $rows->push($child);
                }
            }

            foreach ($standalonePayments as $sp) {
                $rows->push($sp);
            }
        }

        // ── PURCHASE SIDE ────────────────────────────────────────────
        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {

            $purchaseInvoices = PurchaseInvoice::where('party_id', $id)
                ->where('status', '!=', 'draft')
                ->orderBy('invoice_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Build invoice number map
            $invoiceNumberMap = $purchaseInvoices->pluck('invoice_number', '_id')
                ->mapWithKeys(fn($num, $id) => [(string)$id => $num])
                ->toArray();

            $allPaymentsOut  = $this->getPurchasePaymentsOut($id);
            $allDebitNotes   = DebitNote::where('party_id', $id)->get();
            $allDebitRefunds = SalesPayment::where('party_id', $id)
                ->where('payment_subtype', 'debit_refund')
                ->get();

            $paymentsOutByInvoice  = [];
            $standalonePaymentsOut = [];

            foreach ($allPaymentsOut as $pmt) {
                $map = $this->resolvePaymentInvoiceMap($pmt, 'purchase_invoice_id');

                $voucherNo = $this->resolvePaymentVoucherNumber($pmt, $invoiceNumberMap, 'purchase_invoice_id');

                if (!empty($map)) {
                    foreach ($map as $invId => $amount) {
                        $paymentsOutByInvoice[$invId][] = [
                            'raw_date'    => $pmt->payment_date,
                            'date'        => $this->formatDate($pmt->payment_date),
                            'type'        => 'Payment Out',
                            'type_badge'  => 'payment_out',
                            'number'      => $voucherNo,
                            'amount'      => $amount,
                            'amount_type' => 'debit',
                            'status'      => $pmt->status ?? 'completed',
                            'is_child'    => true,
                            'is_parent'   => false,
                        ];
                    }
                } else {
                    $standalonePaymentsOut[] = [
                        'raw_date'    => $pmt->payment_date,
                        'date'        => $this->formatDate($pmt->payment_date),
                        'type'        => 'Payment Out',
                        'type_badge'  => 'payment_out',
                        'number'      => $voucherNo,
                        'amount'      => $this->toFloat($pmt->amount),
                        'amount_type' => 'debit',
                        'status'      => $pmt->status ?? 'completed',
                        'is_child'    => false,
                        'is_parent'   => false,
                    ];
                }
            }

            // Debit notes + their refunds by invoice
            $dnByInvoice = [];
            foreach ($allDebitNotes as $dn) {
                $invId = (string)($dn->purchase_invoice_id ?? '');
                if (!$invId) continue;

                $dnByInvoice[$invId][] = [
                    'raw_date'    => $dn->debit_date,
                    'date'        => $this->formatDate($dn->debit_date),
                    'type'        => 'Debit Note',
                    'type_badge'  => 'debit_note',
                    'number'      => $dn->debit_note_number,
                    'amount'      => $this->toFloat($dn->amount),
                    'amount_type' => 'debit',
                    'status'      => $dn->status,
                    'is_child'    => true,
                    'is_parent'   => false,
                ];

                foreach ($allDebitRefunds as $refund) {
                    $refAllocs = $this->parseAllocations($refund->allocations);
                    foreach ($refAllocs as $ra) {
                        if (($ra['type'] ?? '') === 'debit_note'
                            && (string)($ra['debit_note_id'] ?? '') === (string)$dn->_id) {
                            $dnByInvoice[$invId][] = [
                                'raw_date'    => $refund->payment_date,
                                'date'        => $this->formatDate($refund->payment_date),
                                'type'        => 'Debit Refund',
                                'type_badge'  => 'payment_in',
                                'number'      => $refund->payment_number,
                                'amount'      => $this->toFloat($ra['amount'] ?? $refund->amount),
                                'amount_type' => 'credit',
                                'status'      => $refund->status,
                                'is_child'    => true,
                                'is_parent'   => false,
                            ];
                            break;
                        }
                    }
                }
            }

            foreach ($purchaseInvoices as $inv) {
                $invId = (string) $inv->_id;

                $rows->push([
                    'date'        => $this->formatDate($inv->invoice_date),
                    'raw_date'    => $inv->invoice_date,
                    'type'        => 'Purchase Invoice',
                    'type_badge'  => 'purchase',
                    'number'      => $inv->invoice_number,
                    'amount'      => $this->toFloat($inv->grand_total),
                    'amount_type' => 'credit',
                    'status'      => $inv->payment_status ?? $inv->status,
                    'is_child'    => false,
                    'is_parent'   => true,
                    'balance'     => $this->toFloat($inv->balance_amount),
                ]);

                $children = collect();
                foreach ($paymentsOutByInvoice[$invId] ?? [] as $c) { $children->push($c); }
                foreach ($dnByInvoice[$invId]           ?? [] as $c) { $children->push($c); }

                foreach ($children->sortBy(fn($c) => $this->sortableDate($c['raw_date'])) as $child) {
                    $rows->push($child);
                }
            }

            foreach ($standalonePaymentsOut as $sp) {
                $rows->push($sp);
            }
        }

        return $rows;
    }

    // =========================================================
    //  TAB 2 — LEDGER STATEMENT (running balance, date ASC)
    // =========================================================

private function buildLedger(string $partyType, string $id, $party): \Illuminate\Support\Collection
{
    $entries = collect();
    $openingBalance = $this->toFloat($party->opening_balance ?? 0);

    // For grouping invoices with their related entries
    $groupedEntries = [];

    // ── SALES SIDE (Customer, Dealer, Distributor) ──
    if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {
        $salesInvoices = SalesInvoice::where('party_id', $id)
            ->where('status', '!=', 'draft')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $invNumMap = $salesInvoices->pluck('invoice_number', '_id')
            ->mapWithKeys(fn($num, $id) => [(string)$id => $num])
            ->toArray();

        // ✅ Regular payments (money received from customer) - CREDIT
        $regularPayments = $this->getSalesPaymentsIn($id);

        // ✅ Refund payments to customer - DEBIT (because we are paying them back)
        // Ye PurchasePayment table mein hai with credit_refund
        $refundPayments = PurchasePayment::where('party_id', $id)
            ->where('payment_subtype', 'credit_refund')
            ->get();

        $creditNotes = CreditNote::where('party_id', $id)->get();

        // Group regular payments by invoice (CREDIT entries)
       // Group regular payments by invoice (CREDIT entries)
$paymentsByInvoice = [];
foreach ($regularPayments as $pmt) {
    $map = $this->resolvePaymentInvoiceMap($pmt, 'sales_invoice_id');
    $voucherNo = $this->resolvePaymentVoucherNumber($pmt, $invNumMap, 'sales_invoice_id');

    if (!empty($map)) {
        foreach ($map as $invId => $amount) {
            $paymentsByInvoice[$invId][] = [
                'raw_date' => $pmt->payment_date,
                'date' => $this->formatDate($pmt->payment_date),
                'voucher_type' => 'Payment Received',
                'voucher_no' => $voucherNo,
                'debit' => 0.0,
                'credit' => $this->toFloat($amount),
                'tds_by_party' => $this->extractTDS($this->parseAllocations($pmt->allocations), 'tds_by_party'),
                'tds_by_self' => $this->extractTDS($this->parseAllocations($pmt->allocations), 'tds_by_self'),
                'is_child' => true,
                'parent_id' => $invId,
                'is_refund' => false,
            ];
        }
    }

    // ✅ Opening balance allocation check karo
    $allocations = $this->parseAllocations($pmt->allocations);
    foreach ($allocations as $alloc) {
        if (($alloc['type'] ?? '') === 'opening_balance') {
            $groupedEntries[] = [
                'raw_date' => $pmt->payment_date,
                'date' => $this->formatDate($pmt->payment_date),
                'voucher_type' => 'Opening Balance Payment',
                'voucher_no' => $pmt->payment_number ?? '—',
                'debit' => 0.0,
                'credit' => $this->toFloat($alloc['amount'] ?? 0),
                'tds_by_party' => 0.0,
                'tds_by_self' => 0.0,
                'is_parent' => false,
                'is_child' => false,
                'is_refund' => false,
                'parent_id' => null,
            ];
        }
    }
}

        // Initialize arrays for credit notes
        $creditNotesByInvoice = [];
        $standaloneCreditNotes = [];

        // Build credit note entries (CREDIT entries - we owe customer)
        foreach ($creditNotes as $cn) {
            $invId = (string)($cn->sales_invoice_id ?? '');
            $cnAmount = $this->toFloat($cn->amount);

            // Credit note as parent entry - CREDIT
            $creditNoteEntry = [
                'raw_date' => $cn->credit_date,
                'date' => $this->formatDate($cn->credit_date),
                'voucher_type' => 'Credit Note',
                'voucher_no' => $cn->credit_note_number,
                'debit' => 0.0,
                'credit' => $cnAmount,  // ✅ CREDIT - We owe customer
                'tds_by_party' => 0.0,
                'tds_by_self' => 0.0,
                'is_child' => false,
                'is_parent' => true,
                'is_credit_note' => true,
                'parent_id' => $invId,
            ];

            if ($invId) {
                if (!isset($creditNotesByInvoice[$invId])) {
                    $creditNotesByInvoice[$invId] = [];
                }
                $creditNotesByInvoice[$invId][] = $creditNoteEntry;

                // Find refund payments linked to this credit note - DEBIT
                foreach ($refundPayments as $pmt) {
                    $allocations = $this->parseAllocations($pmt->allocations);
                    foreach ($allocations as $alloc) {
                        if (($alloc['type'] ?? '') === 'credit_note' &&
                            (string)($alloc['credit_note_id'] ?? '') === (string)$cn->_id) {

                            // Refund as child entry under credit note - DEBIT (we pay them)
                            $creditNotesByInvoice[$invId][] = [
                                'raw_date' => $pmt->payment_date,
                                'date' => $this->formatDate($pmt->payment_date),
                                'voucher_type' => 'Refund Payment',
                                'voucher_no' => $pmt->payment_number ?? '—',
                                'debit' => $this->toFloat($alloc['amount'] ?? $pmt->amount),  // ✅ DEBIT - We pay back
                                'credit' => 0.0,
                                'tds_by_party' => 0.0,
                                'tds_by_self' => 0.0,
                                'is_child' => true,
                                'is_parent' => false,
                                'is_refund' => true,
                                'parent_id' => $invId,
                            ];
                        }
                    }
                }
            } else {
                $standaloneCreditNotes[] = $creditNoteEntry;

                // Add refunds under standalone credit note - DEBIT
                foreach ($refundPayments as $pmt) {
                    $allocations = $this->parseAllocations($pmt->allocations);
                    foreach ($allocations as $alloc) {
                        if (($alloc['type'] ?? '') === 'credit_note' &&
                            (string)($alloc['credit_note_id'] ?? '') === (string)$cn->_id) {

                            $standaloneCreditNotes[] = [
                                'raw_date' => $pmt->payment_date,
                                'date' => $this->formatDate($pmt->payment_date),
                                'voucher_type' => 'Refund Payment',
                                'voucher_no' => $pmt->payment_number ?? '—',
                                'debit' => $this->toFloat($alloc['amount'] ?? $pmt->amount),  // ✅ DEBIT - We pay back
                                'credit' => 0.0,
                                'tds_by_party' => 0.0,
                                'tds_by_self' => 0.0,
                                'is_child' => true,
                                'is_parent' => false,
                                'is_refund' => true,
                                'parent_id' => null,
                            ];
                        }
                    }
                }
            }
        }

        // Build grouped entries for invoices
        foreach ($salesInvoices as $inv) {
            $invId = (string)$inv->_id;

            // Sale Invoice - DEBIT
            $groupedEntries[] = [
                'raw_date' => $inv->invoice_date,
                'date' => $this->formatDate($inv->invoice_date),
                'voucher_type' => 'Sale Invoice',
                'voucher_no' => $inv->invoice_number,
                'debit' => $this->toFloat($inv->grand_total),  // ✅ DEBIT - Customer owes us
                'credit' => 0.0,
                'tds_by_party' => 0.0,
                'tds_by_self' => 0.0,
                'is_parent' => true,
                'parent_id' => $invId,
                'is_refund' => false,
            ];

            $children = [];
            if (isset($paymentsByInvoice[$invId])) {
                $children = array_merge($children, $paymentsByInvoice[$invId]);
            }
            if (isset($creditNotesByInvoice[$invId])) {
                $children = array_merge($children, $creditNotesByInvoice[$invId]);
            }

            usort($children, function($a, $b) {
                return strtotime($a['raw_date']) - strtotime($b['raw_date']);
            });

            foreach ($children as $child) {
                $groupedEntries[] = $child;
            }
        }

        // Add standalone credit notes
        if (count($standaloneCreditNotes) > 0) {
            foreach ($standaloneCreditNotes as $cn) {
                $groupedEntries[] = $cn;
            }
        }

        // Add standalone refunds (not linked to any credit note)
        $usedRefundIds = [];
        foreach ($groupedEntries as $entry) {
            if (($entry['is_refund'] ?? false) && isset($entry['voucher_no']) && $entry['voucher_no'] !== '—') {
                $usedRefundIds[] = $entry['voucher_no'];
            }
        }

        foreach ($refundPayments as $pmt) {
            $voucherNo = $pmt->payment_number ?? '—';
            if (!in_array($voucherNo, $usedRefundIds)) {
                $groupedEntries[] = [
                    'raw_date' => $pmt->payment_date,
                    'date' => $this->formatDate($pmt->payment_date),
                    'voucher_type' => 'Refund Payment',
                    'voucher_no' => $voucherNo,
                    'debit' => $this->toFloat($pmt->amount),  // ✅ DEBIT - We pay back
                    'credit' => 0.0,
                    'tds_by_party' => 0.0,
                    'tds_by_self' => 0.0,
                    'is_child' => false,
                    'is_parent' => false,
                    'is_refund' => true,
                    'parent_id' => null,
                ];
            }
        }
    }

    // ── PURCHASE SIDE (Vendor, Dealer, Distributor) ──
    if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {
        $purchaseInvoices = PurchaseInvoice::where('party_id', $id)
            ->where('status', '!=', 'draft')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $invNumMap = $purchaseInvoices->pluck('invoice_number', '_id')
            ->mapWithKeys(fn($num, $id) => [(string)$id => $num])
            ->toArray();

        // ✅ Regular payments (money paid to vendor) - DEBIT
        $regularPayments = $this->getPurchasePaymentsOut($id);

        // ✅ Refund payments received from vendor - CREDIT (vendor pays us back)
        // Ye SalesPayment table mein hai with debit_refund
        $refundPayments = SalesPayment::where('party_id', $id)
            ->where('payment_subtype', 'debit_refund')
            ->get();

        $debitNotes = DebitNote::where('party_id', $id)->get();

        // Group regular payments by invoice (DEBIT entries)
        $paymentsByInvoice = [];
        foreach ($regularPayments as $pmt) {
            $map = $this->resolvePaymentInvoiceMap($pmt, 'purchase_invoice_id');
            $voucherNo = $this->resolvePaymentVoucherNumber($pmt, $invNumMap, 'purchase_invoice_id');

            if (!empty($map)) {
                foreach ($map as $invId => $amount) {
                    $paymentsByInvoice[$invId][] = [
                        'raw_date' => $pmt->payment_date,
                        'date' => $this->formatDate($pmt->payment_date),
                        'voucher_type' => 'Payment Made',
                        'voucher_no' => $voucherNo,
                        'debit' => $this->toFloat($amount),  // ✅ DEBIT - We paid vendor
                        'credit' => 0.0,
                        'tds_by_party' => $this->extractTDS($this->parseAllocations($pmt->allocations), 'tds_by_party'),
                        'tds_by_self' => $this->extractTDS($this->parseAllocations($pmt->allocations), 'tds_by_self'),
                        'is_child' => true,
                        'parent_id' => $invId,
                        'is_refund' => false,
                    ];
                }
            }
        }

        // Initialize arrays for debit notes
        $debitNotesByInvoice = [];
        $standaloneDebitNotes = [];

        // Build debit note entries (DEBIT entries - vendor owes us)
        foreach ($debitNotes as $dn) {
            $invId = (string)($dn->purchase_invoice_id ?? '');
            $dnAmount = $this->toFloat($dn->amount);

            $debitNoteEntry = [
                'raw_date' => $dn->debit_date,
                'date' => $this->formatDate($dn->debit_date),
                'voucher_type' => 'Debit Note',
                'voucher_no' => $dn->debit_note_number,
                'debit' => $dnAmount,  // ✅ DEBIT - Vendor owes us
                'credit' => 0.0,
                'tds_by_party' => 0.0,
                'tds_by_self' => 0.0,
                'is_child' => false,
                'is_parent' => true,
                'is_debit_note' => true,
                'parent_id' => $invId,
            ];

            if ($invId) {
                if (!isset($debitNotesByInvoice[$invId])) {
                    $debitNotesByInvoice[$invId] = [];
                }
                $debitNotesByInvoice[$invId][] = $debitNoteEntry;

                // Find refund payments linked to this debit note - CREDIT (vendor pays us back)
                foreach ($refundPayments as $pmt) {
                    $allocations = $this->parseAllocations($pmt->allocations);
                    foreach ($allocations as $alloc) {
                        if (($alloc['type'] ?? '') === 'debit_note' &&
                            (string)($alloc['debit_note_id'] ?? '') === (string)$dn->_id) {

                            // Refund as child entry under debit note - CREDIT
                            $debitNotesByInvoice[$invId][] = [
                                'raw_date' => $pmt->payment_date,
                                'date' => $this->formatDate($pmt->payment_date),
                                'voucher_type' => 'Refund Received',
                                'voucher_no' => $pmt->payment_number ?? '—',
                                'debit' => 0.0,
                                'credit' => $this->toFloat($alloc['amount'] ?? $pmt->amount),  // ✅ CREDIT - Vendor pays us
                                'tds_by_party' => 0.0,
                                'tds_by_self' => 0.0,
                                'is_child' => true,
                                'is_parent' => false,
                                'is_refund' => true,
                                'parent_id' => $invId,
                            ];
                        }
                    }
                }
            } else {
                $standaloneDebitNotes[] = $debitNoteEntry;

                // Add refunds under standalone debit note - CREDIT
                foreach ($refundPayments as $pmt) {
                    $allocations = $this->parseAllocations($pmt->allocations);
                    foreach ($allocations as $alloc) {
                        if (($alloc['type'] ?? '') === 'debit_note' &&
                            (string)($alloc['debit_note_id'] ?? '') === (string)$dn->_id) {

                            $standaloneDebitNotes[] = [
                                'raw_date' => $pmt->payment_date,
                                'date' => $this->formatDate($pmt->payment_date),
                                'voucher_type' => 'Refund Received',
                                'voucher_no' => $pmt->payment_number ?? '—',
                                'debit' => 0.0,
                                'credit' => $this->toFloat($alloc['amount'] ?? $pmt->amount),  // ✅ CREDIT - Vendor pays us
                                'tds_by_party' => 0.0,
                                'tds_by_self' => 0.0,
                                'is_child' => true,
                                'is_parent' => false,
                                'is_refund' => true,
                                'parent_id' => null,
                            ];
                        }
                    }
                }
            }
        }

        // Build grouped entries for invoices
        foreach ($purchaseInvoices as $inv) {
            $invId = (string)$inv->_id;

            // Purchase Invoice - CREDIT
            $groupedEntries[] = [
                'raw_date' => $inv->invoice_date,
                'date' => $this->formatDate($inv->invoice_date),
                'voucher_type' => 'Purchase Invoice',
                'voucher_no' => $inv->invoice_number,
                'debit' => 0.0,
                'credit' => $this->toFloat($inv->grand_total),  // ✅ CREDIT - We owe vendor
                'tds_by_party' => 0.0,
                'tds_by_self' => 0.0,
                'is_parent' => true,
                'parent_id' => $invId,
                'is_refund' => false,
            ];

            $children = [];
            if (isset($paymentsByInvoice[$invId])) {
                $children = array_merge($children, $paymentsByInvoice[$invId]);
            }
            if (isset($debitNotesByInvoice[$invId])) {
                $children = array_merge($children, $debitNotesByInvoice[$invId]);
            }

            usort($children, function($a, $b) {
                return strtotime($a['raw_date']) - strtotime($b['raw_date']);
            });

            foreach ($children as $child) {
                $groupedEntries[] = $child;
            }
        }

        // Add standalone debit notes
        if (count($standaloneDebitNotes) > 0) {
            foreach ($standaloneDebitNotes as $dn) {
                $groupedEntries[] = $dn;
            }
        }

        // Add standalone refunds (not linked to any debit note)
        $usedRefundIds = [];
        foreach ($groupedEntries as $entry) {
            if (($entry['is_refund'] ?? false) && isset($entry['voucher_no']) && $entry['voucher_no'] !== '—') {
                $usedRefundIds[] = $entry['voucher_no'];
            }
        }

        foreach ($refundPayments as $pmt) {
            $voucherNo = $pmt->payment_number ?? '—';
            if (!in_array($voucherNo, $usedRefundIds)) {
                $groupedEntries[] = [
                    'raw_date' => $pmt->payment_date,
                    'date' => $this->formatDate($pmt->payment_date),
                    'voucher_type' => 'Refund Received',
                    'voucher_no' => $voucherNo,
                    'debit' => 0.0,
                    'credit' => $this->toFloat($pmt->amount),  // ✅ CREDIT - Vendor pays us
                    'tds_by_party' => 0.0,
                    'tds_by_self' => 0.0,
                    'is_child' => false,
                    'is_parent' => false,
                    'is_refund' => true,
                    'parent_id' => null,
                ];
            }
        }
    }

    // Now add opening balance and calculate running balance
    $result = collect();
    $balance = $openingBalance;

    // Add opening balance
    $result->push([
        'date' => null,
        'raw_date' => null,
        'voucher_type' => 'Opening Balance',
        'voucher_no' => '—',
        'debit' => 0.0,
        'credit' => 0.0,
        'tds_by_party' => 0.0,
        'tds_by_self' => 0.0,
        'balance' => $balance,
        'is_opening' => true,
        'is_parent' => false,
    ]);

    // Process entries in the order they were added
    foreach ($groupedEntries as $entry) {
        $balance = round($balance + $entry['debit'] - $entry['credit'], 2);
        $result->push([
            'date' => $entry['date'],
            'raw_date' => $entry['raw_date'],
            'voucher_type' => $entry['voucher_type'],
            'voucher_no' => $entry['voucher_no'],
            'debit' => $entry['debit'],
            'credit' => $entry['credit'],
            'tds_by_party' => $entry['tds_by_party'],
            'tds_by_self' => $entry['tds_by_self'],
            'balance' => $balance,
            'is_opening' => false,
            'is_parent' => $entry['is_parent'] ?? false,
            'is_child' => $entry['is_child'] ?? false,
        ]);
    }

    return $result;
}

    // =========================================================
    //  TAB 3 — PROFILE
    // =========================================================

    private function buildProfile(string $partyType, $party): array
    {
        if ($partyType === 'vendor') {
            $billing  = $party->addresses->where('type', 'billing')->where('is_default', true)->first()
                     ?? $party->addresses->where('type', 'billing')->first();
            $shipping = $party->addresses->where('type', 'shipping')->where('is_default', true)->first()
                     ?? $party->addresses->where('type', 'shipping')->first();
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

        $billing  = $party->addresses->where('type', 'billing')->where('is_default', true)->first()
                 ?? $party->addresses->where('type', 'billing')->first();
        $shipping = $party->addresses->where('type', 'shipping')->where('is_default', true)->first()
                 ?? $party->addresses->where('type', 'shipping')->first();
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

    // Add this helper method to check if a payment is a refund
private function isPaymentRefund($payment, string $type): bool
{
    if ($type === 'sales_payment') {
        // For SalesPayment (payment_in) - refunds have debit_refund subtype
        return ($payment->payment_subtype ?? '') === 'debit_refund';
    } else {
        // For PurchasePayment (payment_out) - refunds have credit_refund subtype
        return ($payment->payment_subtype ?? '') === 'credit_refund';
    }
}

// Add this to check if a payment is linked to credit note (refund to customer)
private function isLinkedToCreditNote($payment): bool
{
    $allocations = $this->parseAllocations($payment->allocations);
    foreach ($allocations as $alloc) {
        if (($alloc['type'] ?? '') === 'credit_note') {
            return true;
        }
    }
    return false;
}

// Add this to check if a payment is linked to debit note (refund from vendor)
private function isLinkedToDebitNote($payment): bool
{
    $allocations = $this->parseAllocations($payment->allocations);
    foreach ($allocations as $alloc) {
        if (($alloc['type'] ?? '') === 'debit_note') {
            return true;
        }
    }
    return false;
}
    // =========================================================
    //  TAB 4 — ITEM WISE REPORT
    // =========================================================

    private function buildItemReport(string $partyType, string $id): \Illuminate\Support\Collection
    {
        $productMap = [];

        if (in_array($partyType, ['customer', 'dealer', 'distributor'])) {
            foreach (SalesInvoice::where('party_id', $id)->where('status', '!=', 'draft')->with('items')->get() as $inv) {
                foreach ($inv->items as $item) {
                    $key  = $item->sku ?: ((string)($item->product_id ?? '') . '_' . ((string)($item->variant_id ?? '')));
                    $name = $item->variant_name ? $item->product_name . ' - ' . $item->variant_name : $item->product_name;
                    if (!isset($productMap[$key])) {
                        $productMap[$key] = ['product_name' => $name, 'sku' => $item->sku ?? '—', 'hsn' => $item->hsn_sac ?? '—', 'sale_qty' => 0.0, 'sale_amount' => 0.0, 'purchase_qty' => 0.0, 'purchase_amount' => 0.0];
                    }
                    $productMap[$key]['sale_qty']    += (float) $item->quantity;
                    $productMap[$key]['sale_amount'] += $this->toFloat($item->total);
                }
            }
        }

        if (in_array($partyType, ['vendor', 'dealer', 'distributor'])) {
            foreach (PurchaseInvoice::where('party_id', $id)->where('status', '!=', 'draft')->with('items')->get() as $inv) {
                foreach ($inv->items as $item) {
                    $key = $item->sku ?: ((string)($item->product_id ?? '') . '_' . ((string)($item->variant_id ?? '')));
                    if (!isset($productMap[$key])) {
                        $productMap[$key] = ['product_name' => $item->product_name, 'sku' => $item->sku ?? '—', 'hsn' => $item->hsn_sac ?? '—', 'sale_qty' => 0.0, 'sale_amount' => 0.0, 'purchase_qty' => 0.0, 'purchase_amount' => 0.0];
                    }
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
    //  SUMMARY
    // =========================================================

    private function buildSummary(\Illuminate\Support\Collection $ledger): array
    {
        $non = $ledger->where('is_opening', false);
        return [
            'total_debit'     => round($non->sum('debit'), 2),
            'total_credit'    => round($non->sum('credit'), 2),
            'closing_balance' => round($ledger->last()['balance'] ?? 0, 2),
            'opening_balance' => round($ledger->first()['balance'] ?? 0, 2),
        ];
    }

    // =========================================================
    //  HELPERS
    // =========================================================

    private function parseAllocations($allocations): array
    {
        if (is_string($allocations)) return json_decode($allocations, true) ?? [];
        return is_array($allocations) ? $allocations : [];
    }

    private function extractTDS(array $allocations, string $key): float
    {
        return collect($allocations)
            ->filter(fn($a) => isset($a['type']) && $a['type'] === $key)
            ->sum('amount');
    }

    private function sortableDate($date): string
    {
        if (!$date) return '0000-00-00';
        if ($date instanceof \Carbon\Carbon)            return $date->format('Y-m-d H:i:s');
        if ($date instanceof \MongoDB\BSON\UTCDateTime) return $date->toDateTime()->format('Y-m-d H:i:s');
        try { return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s'); }
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
        if ($date instanceof \Carbon\Carbon)            return $date->format('d-m-Y');
        if ($date instanceof \MongoDB\BSON\UTCDateTime) return $date->toDateTime()->format('d-m-Y');
        try { return \Carbon\Carbon::parse($date)->format('d-m-Y'); }
        catch (\Exception $e) { return (string) $date; }
    }
    public function print(Request $request, string $partyType, string $id)
{
    if ($partyType === 'vendor') {
        $party = Vendor::with('addresses')->findOrFail($id);
    } else {
        $party = Customer::with('addresses')->findOrFail($id);
    }

    $ledger = $this->buildLedger($partyType, $id, $party);
    $summary = $this->buildSummary($ledger);

    // Date range (optional)
    $fromDate = $request->get('from_date', 'Opening');
    $toDate = $request->get('to_date', now()->format('d-m-Y'));

    return view('admin.ledger.print', compact(
        'party', 'partyType', 'ledger', 'summary', 'fromDate', 'toDate'
    ));
}
}
