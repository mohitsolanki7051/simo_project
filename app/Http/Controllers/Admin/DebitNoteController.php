<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DebitNote;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebitNoteController extends Controller
{
/**
 * Display a listing of debit notes.
 */
public function index(Request $request)
{
    $query = DebitNote::with(['party', 'purchaseInvoice', 'purchaseReturn', 'creator'])
        ->orderBy('created_at', 'desc');

    // Date filter
    if ($request->filled('date_from')) {
        $query->whereDate('debit_date', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('debit_date', '<=', $request->date_to);
    }

    // Debit note number search
    if ($request->filled('debit_note_number')) {
        $query->where('debit_note_number', 'like', '%' . $request->debit_note_number . '%');
    }

    // Invoice number search
    if ($request->filled('invoice_number')) {
        $invoiceIds = PurchaseInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            ->pluck('_id');
        $query->whereIn('purchase_invoice_id', $invoiceIds);
    }

    // Party filter (vendor/dealer/distributor)
    if ($request->filled('party_id')) {
        $query->where('party_id', $request->party_id);
    }

    // Status filter - Updated to include new statuses
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $debitNotes = $query->paginate(20)->withQueryString();

    /* ================= STATS QUERY - ALL FILTERS APPLY ================= */
    $statsQuery = DebitNote::query();

    // Apply ALL filters to stats query
    if ($request->filled('date_from')) {
        $statsQuery->whereDate('debit_date', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $statsQuery->whereDate('debit_date', '<=', $request->date_to);
    }
    if ($request->filled('debit_note_number')) {
        $statsQuery->where('debit_note_number', 'like', '%' . $request->debit_note_number . '%');
    }
    if ($request->filled('invoice_number')) {
        $invoiceIds = PurchaseInvoice::where('invoice_number', 'like', '%' . $request->invoice_number . '%')
            ->pluck('_id');
        $statsQuery->whereIn('purchase_invoice_id', $invoiceIds);
    }
    if ($request->filled('party_id')) {
        $statsQuery->where('party_id', $request->party_id);
    }
    if ($request->filled('status')) {
        $statsQuery->where('status', $request->status);
    }

    // Calculate stats
    $totalAmountRaw = (clone $statsQuery)->sum('amount');
    $totalUsedRaw = (clone $statsQuery)->sum('used_amount');
    $totalRemainingRaw = (clone $statsQuery)->sum('remaining_amount');

    $totalAmount = $this->decimalToFloat($totalAmountRaw);
    $totalUsed = $this->decimalToFloat($totalUsedRaw);
    $totalRemaining = $this->decimalToFloat($totalRemainingRaw);

    // Count queries with filters applied - Updated status counts
   $activeCount = (clone $statsQuery)->where('status', 'active')->count();
    $settledCount = (clone $statsQuery)->where('status', 'settled')->count();
    $partialCount = (clone $statsQuery)->where('status', 'partial')->count();

    // Get active parties for filter dropdown (both vendors and customers)
    $vendors = Vendor::where('status', 'active')->orderBy('company_name')->get();
    $customers = Customer::whereIn('party_type', ['dealer', 'distributor'])
        ->where('status', 'active')
        ->orderBy('name')
        ->get();

    // Merge for dropdown
    $allParties = collect();
    foreach ($vendors as $v) {
        $allParties->push((object)[
            '_id' => $v->_id,
            'name' => $v->company_name,
            'type' => 'vendor'
        ]);
    }
    foreach ($customers as $c) {
        $allParties->push((object)[
            '_id' => $c->_id,
            'name' => $c->name,
            'type' => $c->party_type
        ]);
    }
    $allParties = $allParties->sortBy('name');

    return view('admin.debit-notes.index', compact(
        'debitNotes',
        'totalAmount',
        'totalUsed',
        'totalRemaining',
        'activeCount',
        'settledCount',
        'partialCount',
        'allParties'
    ));
}

    /**
     * Display the specified debit note.
     */
  /**
 * Display the specified debit note.
 */
public function show($id)
{
    $debitNote = DebitNote::with(['purchaseInvoice', 'purchaseReturn', 'items', 'creator'])
        ->findOrFail($id);

    // ✅ Manually load party data
    if ($debitNote->party_type === 'vendor') {
        $debitNote->party = Vendor::find($debitNote->party_id);
    } else {
        $debitNote->party = Customer::find($debitNote->party_id);
    }

    return view('admin.debit-notes.show', compact('debitNote'));
}

    /**
     * Cancel a debit note (only if active)
     */
    public function cancel($id)
    {
        try {
            $debitNote = DebitNote::findOrFail($id);

            if ($debitNote->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active debit notes can be cancelled.'
                ], 400);
            }

            $debitNote->update([
                'status' => 'cancelled'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Debit note cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get party-wise debit notes summary
     */
    public function partySummary($partyId, $partyType = null)
    {
        $query = DebitNote::where('party_id', $partyId)
            ->where('status', 'active');

        if ($partyType) {
            $query->where('party_type', $partyType);
        }

        $debitNotes = $query->get();

        $totalAvailable = $this->decimalToFloat($debitNotes->sum('remaining_amount'));

        return response()->json([
            'success' => true,
            'total_available' => $totalAvailable,
            'debit_notes' => $debitNotes->map(fn($note) => [
                'id' => (string)$note->_id,
                'number' => $note->debit_note_number,
                'amount' => $note->amount_float,
                'used' => $note->used_amount_float,
                'remaining' => $note->remaining_amount_float,
            ])
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
