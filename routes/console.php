<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * --------------------------------------------------------------------------
 * INVOICES:SYNC-BALANCES (Safe Outstanding Balance Sync)
 * --------------------------------------------------------------------------
 * ONLY updates SalesInvoice:
 *   - balance_amount = max(0, grand_total - total_paid)
 *   - payment_status = ('paid' if balance <= 0, else 'partial'/'unpaid')
 *   - Ensures fully paid bills do NOT inflate 'Previous Outstanding'
 *   - Restores any invoice-time payment records so LEDGER is 100% untouched!
 *
 * Usage:
 *   php artisan invoices:sync-balances --fix
 */
Artisan::command('invoices:sync-balances {--invoice= : Filter by Invoice number or ID} {--party= : Filter by Party ID} {--fix : Actually apply changes to database (default is preview only)}', function () {
    $fix = (bool) $this->option('fix');
    $invoiceFilter = $this->option('invoice');
    $partyFilter = $this->option('party');

    $this->line("");
    if ($fix) {
        $this->warn("⚠️  RUNNING IN [FIX MODE] — Updating Invoices for Outstanding Balance (Ledger remains untouched)...");
    } else {
        $this->info("🔍 RUNNING IN [PREVIEW MODE] — Preview only, no changes.");
    }
    $this->line("--------------------------------------------------------------------------------");

    // Safe helper to convert any MongoDB Decimal128 / float / string to float
    $toNum = function ($val): float {
        if ($val instanceof \MongoDB\BSON\Decimal128) {
            return (float) $val->__toString();
        }
        return (float) ($val ?? 0);
    };

    // Query invoices
    $query = \App\Models\SalesInvoice::query();
    if ($invoiceFilter) {
        $query->where(function ($q) use ($invoiceFilter) {
            $q->where('invoice_number', $invoiceFilter)
              ->orWhere('_id', $invoiceFilter);
        });
    }
    if ($partyFilter) {
        $query->where('party_id', $partyFilter);
    }

    $invoices = $query->orderBy('created_at', 'asc')->get();

    if ($invoices->isEmpty()) {
        $this->error("No invoices found.");
        return 0;
    }

    $this->info("Evaluating " . $invoices->count() . " invoices...");
    $this->line("");

    // Cache receipt payments
    $receiptPayments = \App\Models\SalesPayment::where('payment_type', 'payment_in')
        ->whereNotNull('payment_number')
        ->get();

    $updatedInvoices = 0;
    $tableRows = [];

    foreach ($invoices as $inv) {
        // Skip draft and cancelled
        if (in_array($inv->status, ['draft', 'cancelled'])) {
            continue;
        }

        $invIdStr   = (string) $inv->_id;
        $grandTotal = round($toNum($inv->grand_total), 2);

        // Current stored values
        $storedPaid    = round($toNum($inv->total_paid), 2);
        $storedBalance = round($toNum($inv->balance_amount), 2);
        $storedStatus  = (string) $inv->payment_status;

        // 1. Calculate actual paid amount on this invoice
        $actualPaid = $storedPaid;

        // If credit note applied is recorded and higher than total_paid
        $creditApplied = round($toNum($inv->credit_note_applied ?? 0), 2);
        if ($creditApplied > 0 && $actualPaid < $creditApplied) {
            $actualPaid = $creditApplied;
        }

        // Check if any payment-in receipts were allocated to this invoice
        $receiptAllocated = 0.0;
        foreach ($receiptPayments as $rp) {
            foreach ($rp->allocations ?? [] as $alloc) {
                if (($alloc['type'] ?? '') === 'invoice' && ((string)($alloc['invoice_id'] ?? '')) === $invIdStr) {
                    $receiptAllocated += round($toNum($alloc['amount'] ?? 0), 2);
                }
            }
        }
        if ($receiptAllocated > $actualPaid) {
            $actualPaid = $receiptAllocated;
        }

        $actualPaid = min($grandTotal, $actualPaid);

        // 2. Sahi Balance Amount & Payment Status
        $correctBalance = max(0, round($grandTotal - $actualPaid, 2));

        if ($correctBalance <= 0.01) {
            $correctBalance = 0.0;
            $correctStatus  = 'paid';
        } elseif ($actualPaid > 0) {
            $correctStatus  = 'partial';
        } else {
            $correctStatus  = 'unpaid';
        }

        $needsInvoiceUpdate = (abs($storedBalance - $correctBalance) > 0.01)
                           || (abs($storedPaid - $actualPaid) > 0.01)
                           || ($storedStatus !== $correctStatus);

        // 3. Find invoice-time payment record (to restore if previous script changed it)
        $invoiceTimePayment = \App\Models\SalesPayment::where('sales_invoice_id', $inv->_id)
            ->whereNull('payment_number')
            ->first();
        if (!$invoiceTimePayment) {
            $invoiceTimePayment = \App\Models\SalesPayment::where('sales_invoice_id', $invIdStr)
                ->whereNull('payment_number')
                ->first();
        }

        $paymentRestored = false;
        if ($invoiceTimePayment) {
            // If the previous script set status to 'cancelled' or reduced its amount below total_paid:
            if ($invoiceTimePayment->status === 'cancelled' || (abs($toNum($invoiceTimePayment->amount) - $actualPaid) > 0.01 && $actualPaid > 0)) {
                $paymentRestored = true;
            }
        }

        if ($needsInvoiceUpdate || $paymentRestored) {
            $tableRows[] = [
                $inv->invoice_number,
                $inv->party->name ?? 'N/A',
                number_format($grandTotal, 2),
                number_format($storedPaid, 2) . ' → ' . number_format($actualPaid, 2),
                number_format($storedBalance, 2) . ' → ' . number_format($correctBalance, 2),
                $storedStatus . ' → ' . $correctStatus,
                $paymentRestored ? 'Restoring Ledger Payment & Syncing Invoice' : 'Syncing Outstanding Balance'
            ];

            if ($fix) {
                // Update Invoice ONLY
                $inv->total_paid     = $actualPaid;
                $inv->balance_amount = $correctBalance;
                $inv->payment_status = $correctStatus;
                $inv->save();

                // RESTORE invoice payment so LEDGER is 100% original
                if ($invoiceTimePayment && $paymentRestored) {
                    $invoiceTimePayment->amount = $actualPaid;
                    $invoiceTimePayment->status = 'completed';
                    $invoiceTimePayment->allocations = [
                        [
                            'type'           => 'invoice',
                            'invoice_id'     => $invIdStr,
                            'invoice_number' => $inv->invoice_number,
                            'amount'         => $actualPaid,
                            'description'    => 'Invoice payment at time of generation',
                        ]
                    ];
                    $invoiceTimePayment->save();
                }

                $updatedInvoices++;
            }
        }
    }

    if (!empty($tableRows)) {
        $this->table(
            ['Invoice #', 'Party', 'Grand Total', 'Paid Amount', 'Bal Due', 'Status', 'Action'],
            $tableRows
        );
        $this->line("");
        if ($fix) {
            $this->info("✅ Successfully synced {$updatedInvoices} invoices! Outstanding balance is now fixed and Ledger is preserved.");
        } else {
            $this->warn("Found " . count($tableRows) . " invoice(s) to synchronize.");
            $this->info("Run with '--fix' to apply: php artisan invoices:sync-balances --fix");
        }
    } else {
        $this->info("✨ All invoices already have accurate balances! Outstanding balance is fully in sync.");
    }

    $this->line("--------------------------------------------------------------------------------\n");
    return 0;
})->purpose('Synchronize sales invoices balance_amount and total_paid for accurate outstanding balance without altering ledger');


