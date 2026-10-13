<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * --------------------------------------------------------------------------
 * INVOICES:SYNC-BALANCES (Safe Recalculation & Sync Command)
 * --------------------------------------------------------------------------
 * Safely inspects and recalculates:
 *   - balance_amount
 *   - total_paid
 *   - payment_status
 *   - removes double-counted invoice-time payments where credit notes were included
 *
 * Usage:
 *   1) Preview all mismatches (100% READ ONLY, DRY RUN):
 *      php artisan invoices:sync-balances
 *
 *   2) Inspect ONE specific invoice:
 *      php artisan invoices:sync-balances --invoice=INV-0001
 *
 *   3) Actually apply fixes to ONE invoice:
 *      php artisan invoices:sync-balances --invoice=INV-0001 --fix
 *
 *   4) Actually apply fixes to ALL invoices:
 *      php artisan invoices:sync-balances --fix
 */
Artisan::command('invoices:sync-balances {--invoice= : Filter by Invoice number or ID} {--party= : Filter by Party ID} {--fix : Actually apply changes to database (default is preview only)}', function () {
    $fix = (bool) $this->option('fix');
    $invoiceFilter = $this->option('invoice');
    $partyFilter = $this->option('party');

    $this->line("");
    if ($fix) {
        $this->warn("⚠️  RUNNING IN [FIX MODE] — Database changes WILL be applied.");
    } else {
        $this->info("🔍 RUNNING IN [DRY-RUN / PREVIEW MODE] — 100% Read-only, no changes will be made.");
        $this->comment("   Pass '--fix' to apply changes to database.");
    }
    $this->line("--------------------------------------------------------------------------------");

    // Safe helper to convert any MongoDB Decimal128 / float / string to float
    $toNum = function ($val): float {
        if ($val instanceof \MongoDB\BSON\Decimal128) {
            return (float) $val->__toString();
        }
        return (float) ($val ?? 0);
    };

    // Build query
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
        $this->error("No invoices found matching criteria.");
        return 0;
    }

    $this->info("Found " . $invoices->count() . " invoices to evaluate.");
    $this->line("");

    // Cache all receipt-based payments to avoid N+1 queries
    $receiptPayments = \App\Models\SalesPayment::where('payment_type', 'payment_in')
        ->whereNotNull('payment_number')
        ->get();

    $tableRows = [];
    $mismatchCount = 0;
    $fixedCount = 0;

    foreach ($invoices as $inv) {
        // Skip draft and cancelled
        if (in_array($inv->status, ['draft', 'cancelled'])) {
            continue;
        }

        $invIdStr   = (string) $inv->_id;
        $grandTotal = round($toNum($inv->grand_total), 2);

        // 1. Check Invoice-Time payment (created when invoice was generated, payment_number is null)
        $invoiceTimePayment = \App\Models\SalesPayment::where('sales_invoice_id', $inv->_id)
            ->whereNull('payment_number')
            ->first();
        if (!$invoiceTimePayment) {
            $invoiceTimePayment = \App\Models\SalesPayment::where('sales_invoice_id', $invIdStr)
                ->whereNull('payment_number')
                ->first();
        }

        $cashAtGen = 0.0;
        $paymentNeedsCorrection = false;

        if ($invoiceTimePayment) {
            $notes = (string) ($invoiceTimePayment->notes ?? '');
            $pmtAmount = round($toNum($invoiceTimePayment->amount), 2);

            // If notes explicitly mention "Cash paid: ₹...", use that exact cash paid amount!
            if (preg_match('/Cash paid:\s*₹?\s*([\d,]+(?:\.\d+)?)/iu', $notes, $matches)) {
                $cashAtGen = (float) str_replace(',', '', $matches[1]);
            } elseif ($toNum($inv->credit_note_applied) > 0 && abs($pmtAmount - $toNum($inv->total_paid)) < 0.01) {
                // Payment record included credit note! Cash = payment_amount - credit_note_applied
                $cashAtGen = max(0, round($pmtAmount - $toNum($inv->credit_note_applied), 2));
            } else {
                $cashAtGen = $pmtAmount;
            }

            // Did the old invoice-time payment record mistakenly store more than actual cash paid?
            if (abs($pmtAmount - $cashAtGen) > 0.01) {
                $paymentNeedsCorrection = true;
            }
        } else {
            // No payment record at generation. If invoice had total_paid before credit note:
            if ($toNum($inv->credit_note_applied) > 0) {
                $cashAtGen = max(0, round($toNum($inv->total_paid) - $toNum($inv->credit_note_applied), 2));
            }
        }

        // 2. Sum up all payments received later via Payment-In vouchers allocated to this invoice
        $allocatedCash = 0.0;
        foreach ($receiptPayments as $rp) {
            foreach ($rp->allocations ?? [] as $alloc) {
                if (($alloc['type'] ?? '') === 'invoice' && ((string)($alloc['invoice_id'] ?? '')) === $invIdStr) {
                    $allocatedCash += round($toNum($alloc['amount'] ?? 0), 2);
                }
            }
        }

        // 3. Credit Note applied
        $creditApplied = round($toNum($inv->credit_note_applied ?? 0), 2);
        // Fallback: check if credit note specifically references this invoice ID
        if ($creditApplied <= 0.001) {
            $directCnUsed = \App\Models\CreditNote::where(function($q) use ($inv, $invIdStr) {
                $q->where('sales_invoice_id', $inv->_id)->orWhere('sales_invoice_id', $invIdStr);
            })->sum('used_amount');
            if ($toNum($directCnUsed) > 0) {
                $creditApplied = round($toNum($directCnUsed), 2);
            }
        }

        // 4. Sahi Total Paid & Balance Due
        $correctTotalPaid = min($grandTotal, round($cashAtGen + $allocatedCash + $creditApplied, 2));
        $correctBalance   = max(0, round($grandTotal - $correctTotalPaid, 2));

        if ($correctBalance <= 0.01) {
            $correctStatus = 'paid';
            $correctBalance = 0.0;
        } elseif ($correctTotalPaid > 0) {
            $correctStatus = 'partial';
        } else {
            $correctStatus = 'unpaid';
        }

        // 5. Current stored values
        $storedBalance = round($toNum($inv->balance_amount), 2);
        $storedPaid    = round($toNum($inv->total_paid), 2);
        $storedStatus  = (string) $inv->payment_status;

        $hasMismatch = (abs($storedBalance - $correctBalance) > 0.01)
                    || (abs($storedPaid - $correctTotalPaid) > 0.01)
                    || ($storedStatus !== $correctStatus)
                    || $paymentNeedsCorrection;

        if ($hasMismatch) {
            $mismatchCount++;

            $tableRows[] = [
                $inv->invoice_number,
                $inv->party->name ?? 'N/A',
                number_format($grandTotal, 2),
                number_format($storedPaid, 2),
                number_format($correctTotalPaid, 2),
                number_format($storedBalance, 2),
                number_format($correctBalance, 2),
                $storedStatus . ' → ' . $correctStatus,
                $paymentNeedsCorrection ? '⚠️ Payment Record Overstated' : '⚠️ Balance Mismatch'
            ];

            if ($fix) {
                // Apply Fix to Sales Invoice
                $inv->total_paid          = $correctTotalPaid;
                $inv->balance_amount      = $correctBalance;
                $inv->payment_status      = $correctStatus;
                if ($creditApplied > 0 && empty($inv->credit_note_applied)) {
                    $inv->credit_note_applied = $creditApplied;
                }
                $inv->save();

                // Apply Fix to Invoice-Time Payment (remove double-counted credit in ledger)
                if ($invoiceTimePayment && $paymentNeedsCorrection) {
                    if ($cashAtGen > 0.001) {
                        $invoiceTimePayment->amount = $cashAtGen;
                        $invoiceTimePayment->allocations = [
                            [
                                'type'           => 'invoice',
                                'invoice_id'     => $invIdStr,
                                'invoice_number' => $inv->invoice_number,
                                'amount'         => $cashAtGen,
                                'description'    => 'Invoice payment at time of generation (Synced)',
                            ]
                        ];
                        $invoiceTimePayment->save();
                    } else {
                        // Actual cash was 0 — it was purely a credit note adjustment!
                        // Remove or zero out payment so ledger doesn't count it twice!
                        $invoiceTimePayment->amount = 0.0;
                        $invoiceTimePayment->status = 'cancelled';
                        $invoiceTimePayment->allocations = [];
                        $invoiceTimePayment->save();
                    }
                }

                $fixedCount++;
            }
        }
    }

    if (!empty($tableRows)) {
        $this->table(
            ['Invoice #', 'Party', 'Grand Total', 'Old Paid', 'New Paid', 'Old Bal Due', 'New Bal Due', 'Status Change', 'Issue'],
            $tableRows
        );
        $this->line("");
        if ($fix) {
            $this->info("✅ Successfully updated {$fixedCount} invoices in the database!");
        } else {
            $this->warn("Found {$mismatchCount} invoice(s) with mismatched balances.");
            $this->info("👉 To apply these fixes to the database, run:");
            if ($invoiceFilter) {
                $this->comment("   php artisan invoices:sync-balances --invoice={$invoiceFilter} --fix");
            } else {
                $this->comment("   php artisan invoices:sync-balances --fix");
            }
        }
    } else {
        $this->info("✨ All evaluated invoices already match correct balances! No discrepancies found.");
    }

    $this->line("--------------------------------------------------------------------------------\n");
    return 0;
})->purpose('Recalculate and synchronize sales invoices balance_amount, total_paid and payment_status');

