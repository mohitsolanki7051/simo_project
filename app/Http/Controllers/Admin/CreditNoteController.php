<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditNoteController extends Controller
{
    /**
     * Display a listing of credit notes.
     */
    public function index(Request $request)
    {
        $query = CreditNote::with(['party', 'invoice', 'salesReturn', 'creator'])
            ->orderBy('created_at', 'desc');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('credit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('credit_date', '<=', $request->date_to);
        }

        // Credit note number search
        if ($request->filled('credit_note_number')) {
            $query->where('credit_note_number', 'like', '%' . $request->credit_note_number . '%');
        }

        // Invoice number search
        if ($request->filled('invoice_number')) {
            $invoiceIds = SalesInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
                ->pluck('_id');
            $query->whereIn('sales_invoice_id', $invoiceIds);
        }

        // Party filter
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $creditNotes = $query->paginate(20)->withQueryString();

        /* ================= STATS QUERY - ALL FILTERS APPLY ================= */
        $statsQuery = CreditNote::query();

        // Apply ALL filters to stats query (same as main query)
        if ($request->filled('date_from')) {
            $statsQuery->whereDate('credit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate('credit_date', '<=', $request->date_to);
        }
        if ($request->filled('credit_note_number')) {
            $statsQuery->where('credit_note_number', 'like', '%' . $request->credit_note_number . '%');
        }
        if ($request->filled('invoice_number')) {
            $invoiceIds = SalesInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
                ->pluck('_id');
            $statsQuery->whereIn('sales_invoice_id', $invoiceIds);
        }
        if ($request->filled('party_id')) {
            $statsQuery->where('party_id', $request->party_id);
        }
        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }

        // Calculate stats with Decimal128 conversion
        $totalAmountRaw = (clone $statsQuery)->sum('amount');
        $totalUsedRaw = (clone $statsQuery)->sum('used_amount');
        $totalRemainingRaw = (clone $statsQuery)->sum('remaining_amount');

        $totalAmount = $this->decimalToFloat($totalAmountRaw);
        $totalUsed = $this->decimalToFloat($totalUsedRaw);
        $totalRemaining = $this->decimalToFloat($totalRemainingRaw);

        // Count queries with filters applied
        $activeCountQuery = clone $statsQuery;
        $usedCountQuery = clone $statsQuery;
        $cancelledCountQuery = clone $statsQuery;

        $activeCount = $activeCountQuery->where('status', 'active')->count();
        $usedCount = $usedCountQuery->where('status', 'used')->count();
        $cancelledCount = $cancelledCountQuery->where('status', 'cancelled')->count();

        // Get active parties for filter dropdown
        $parties = Customer::where('status', 'active')->orderBy('name')->get();

        return view('admin.credit-notes.index', compact(
            'creditNotes',
            'totalAmount',
            'totalUsed',
            'totalRemaining',
            'activeCount',
            'usedCount',
            'cancelledCount',
            'parties'
        ));
    }

    /**
     * Display the specified credit note.
     */
public function show($id)
{
    $creditNote = CreditNote::with(['party', 'invoice', 'salesReturn', 'items', 'creator'])
        ->findOrFail($id);

    return view('admin.credit-notes.show', compact('creditNote'));
}

    /**
     * Cancel a credit note (only if active)
     */
    public function cancel($id)
    {
        try {
            $creditNote = CreditNote::findOrFail($id);

            if ($creditNote->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active credit notes can be cancelled.'
                ], 400);
            }

            $creditNote->update([
                'status' => 'cancelled'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credit note cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get party-wise credit notes summary
     */
    public function partySummary($partyId)
    {
        $creditNotes = CreditNote::where('party_id', $partyId)
            ->where('status', 'active')
            ->get();

        $totalAvailable = $creditNotes->sum('remaining_amount');
        $totalAvailable = $this->decimalToFloat($totalAvailable);

        return response()->json([
            'success' => true,
            'total_available' => $totalAvailable,
            'credit_notes' => $creditNotes->map(function($note) {
                return [
                    'id' => (string)$note->_id,
                    'number' => $note->credit_note_number,
                    'amount' => $note->amount_float,
                    'used' => $note->used_amount_float,
                    'remaining' => $note->remaining_amount_float,
                ];
            })
        ]);
    }

    /**
     * Helper function to convert Decimal128 to float
     */
    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
