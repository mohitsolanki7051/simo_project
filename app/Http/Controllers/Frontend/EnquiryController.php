<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FrontendEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnquiryController extends Controller
{
    /**
     * Handle submission of enquiry form from website frontend
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:dealer,contact,catalogue,general',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city_state' => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $enquiry = FrontendEnquiry::create([
                'type' => $request->type,
                'name' => $request->name,
                'phone' => $request->phone ?? '',
                'email' => $request->email ?? '',
                'city_state' => $request->city_state ?? '',
                'business_name' => $request->business_name ?? '',
                'message' => $request->message ?? '',
                'status' => 'new',
                'ip_address' => $request->ip(),
            ]);

            Log::info('New Frontend Enquiry received:', $enquiry->toArray());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your enquiry has been received successfully! Our team will contact you soon.',
                    'data' => $enquiry
                ]);
            }

            return redirect()->back()->with('success', 'Your enquiry has been received successfully!');

        } catch (\Exception $e) {
            Log::error('Error saving frontend enquiry: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit enquiry. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to submit enquiry. Please try again.');
        }
    }
}
