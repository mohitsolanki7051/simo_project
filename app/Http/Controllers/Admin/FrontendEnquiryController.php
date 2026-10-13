<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontendEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontendEnquiryController extends Controller
{
    /**
     * List all website enquiries in Admin Panel
     */
    public function index(Request $request)
    {
        $query = FrontendEnquiry::orderBy('created_at', 'desc');

        // Type Filter
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Status Filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search Query (by name, phone, email, city)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city_state', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        $enquiries = $query->paginate(15);

        // Stats Counters
        $totalCount = FrontendEnquiry::count();
        $newCount = FrontendEnquiry::where('status', 'new')->count();
        $dealerCount = FrontendEnquiry::where('type', 'dealer')->count();
        $catalogueCount = FrontendEnquiry::where('type', 'catalogue')->count();

        return view('admin.enquiries.index', compact('enquiries', 'totalCount', 'newCount', 'dealerCount', 'catalogueCount'));
    }

    /**
     * Show enquiry detail JSON for modal
     */
    public function show($id)
    {
        $enquiry = FrontendEnquiry::findOrFail($id);
        return response()->json([
            'success' => true,
            'enquiry' => $enquiry
        ]);
    }

    /**
     * Update status of an enquiry (new, contacted, resolved, closed)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,contacted,resolved,closed'
        ]);

        try {
            $enquiry = FrontendEnquiry::findOrFail($id);
            $enquiry->status = $request->status;
            $enquiry->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Enquiry status updated to ' . strtoupper($request->status),
                    'enquiry' => $enquiry
                ]);
            }

            return redirect()->back()->with('success', 'Enquiry status updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating enquiry status: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update status.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update status.');
        }
    }

    /**
     * Delete an enquiry
     */
    public function destroy($id)
    {
        try {
            $enquiry = FrontendEnquiry::findOrFail($id);
            $enquiry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Enquiry record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete enquiry record.'
            ], 500);
        }
    }
}
