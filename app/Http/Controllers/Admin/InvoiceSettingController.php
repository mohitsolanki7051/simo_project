<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceSettingController extends Controller
{
    /**
     * Display invoice settings form
     */
    public function index()
    {
        $setting = InvoiceSetting::first();
        return view('admin.sales.invoice-settings', compact('setting'));
    }

    /**
     * Store or update invoice settings
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stamp' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:11',
            'branch' => 'nullable|string|max:255',
            'terms_and_conditions' => 'nullable|string',
            'footer_note' => 'nullable|string|max:500'
        ]);

        try {
            // Debug logging
            \Log::info('Invoice Setting Store Request:', $request->all());
            \Log::info('Files in request:', $request->files->all());

            $setting = InvoiceSetting::first();

            if (!$setting) {
                $setting = new InvoiceSetting();
                \Log::info('Creating new InvoiceSetting record');
            } else {
                \Log::info('Updating existing InvoiceSetting record ID: ' . $setting->_id);
            }

            // Prepare data
            $data = [
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'company_phone' => $request->company_phone,
                'company_email' => $request->company_email,
                'gstin' => $request->gstin,
                'pan' => $request->pan,
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'ifsc_code' => $request->ifsc_code,
                'branch' => $request->branch,
                'terms_and_conditions' => $request->terms_and_conditions,
                'footer_note' => $request->footer_note ?? 'This is a computer generated invoice'
            ];

            \Log::info('Data to save:', $data);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                \Log::info('Logo file found:', [
                    'name' => $request->file('logo')->getClientOriginalName(),
                    'size' => $request->file('logo')->getSize(),
                    'mime' => $request->file('logo')->getMimeType()
                ]);

                // Delete old logo if exists
                if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                    Storage::disk('public')->delete($setting->logo_path);
                    \Log::info('Deleted old logo: ' . $setting->logo_path);
                }

                // Store new logo
                $logoPath = $request->file('logo')->store('invoice-settings', 'public');
                $data['logo_path'] = $logoPath;
                \Log::info('New logo path: ' . $logoPath);
            }

            // Handle signature upload
            if ($request->hasFile('signature')) {
                \Log::info('Signature file found');

                // Delete old signature if exists
                if ($setting->signature_path && Storage::disk('public')->exists($setting->signature_path)) {
                    Storage::disk('public')->delete($setting->signature_path);
                }

                // Store new signature
                $signaturePath = $request->file('signature')->store('invoice-settings', 'public');
                $data['signature_path'] = $signaturePath;
            }

            // Handle stamp upload
            if ($request->hasFile('stamp')) {
                \Log::info('Stamp file found');

                // Delete old stamp if exists
                if ($setting->stamp_path && Storage::disk('public')->exists($setting->stamp_path)) {
                    Storage::disk('public')->delete($setting->stamp_path);
                }

                // Store new stamp
                $stampPath = $request->file('stamp')->store('invoice-settings', 'public');
                $data['stamp_path'] = $stampPath;
            }

            // Handle image removals
            if ($request->has('remove_logo') && $request->remove_logo == '1') {
                if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                    Storage::disk('public')->delete($setting->logo_path);
                }
                $data['logo_path'] = null;
                \Log::info('Removed logo as per request');
            }

            if ($request->has('remove_signature') && $request->remove_signature == '1') {
                if ($setting->signature_path && Storage::disk('public')->exists($setting->signature_path)) {
                    Storage::disk('public')->delete($setting->signature_path);
                }
                $data['signature_path'] = null;
            }

            if ($request->has('remove_stamp') && $request->remove_stamp == '1') {
                if ($setting->stamp_path && Storage::disk('public')->exists($setting->stamp_path)) {
                    Storage::disk('public')->delete($setting->stamp_path);
                }
                $data['stamp_path'] = null;
            }

            // Save the data
            $setting->fill($data);
            $setting->save();

            \Log::info('Invoice settings saved successfully. ID: ' . $setting->_id);

            return response()->json([
                'success' => true,
                'message' => 'Invoice settings saved successfully!',
                'data' => $setting
            ]);

        } catch (\Exception $e) {
            \Log::error('Error saving invoice settings: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice settings for display
     */
    public function getSettings()
    {
        $settings = InvoiceSetting::first();
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }
}
