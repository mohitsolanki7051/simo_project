<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontendSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class FrontendSettingController extends Controller
{
    /**
     * Display frontend settings form in Admin Panel
     */
    public function index()
    {
        $setting = FrontendSetting::first();
        if (!$setting) {
            $setting = new FrontendSetting([
                'company_name' => 'Simko',
                'brand_tagline' => 'Light | Smart | Innovation',
                'phone_number' => '+91 98765 43210',
                'email_address' => 'info@simkolights.com',
                'location_address' => 'Jaipur, Rajasthan, India',
                'working_hours' => 'Mon - Sat: 9:00 AM - 6:00 PM',
                'hero_subtitle' => 'Illuminate a Brighter Tomorrow',
                'hero_headline' => 'Lighting for a Better Life',
                'hero_description' => 'High-quality, energy-efficient and beautifully designed lighting solutions for homes, offices and modern spaces.',
            ]);
        }

        return view('admin.settings.frontend-settings', compact('setting'));
    }

    /**
     * Store or update frontend website settings
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'brand_tagline' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:50',
            'email_address' => 'required|email|max:255',
            'location_address' => 'required|string|max:255',
            'working_hours' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_headline' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'catalogue_pdf' => 'nullable|mimes:pdf|max:30720', // max 30MB PDF
            'facebook_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|string|max:255',
        ]);

        try {
            $setting = FrontendSetting::first();

            if (!$setting) {
                $setting = new FrontendSetting();
            }

            $data = [
                'company_name' => $request->company_name,
                'brand_tagline' => $request->brand_tagline ?? 'Light | Smart | Innovation',
                'phone_number' => $request->phone_number,
                'email_address' => $request->email_address,
                'location_address' => $request->location_address,
                'working_hours' => $request->working_hours ?? 'Mon - Sat: 9:00 AM - 6:00 PM',
                'hero_subtitle' => $request->hero_subtitle ?? 'Illuminate a Brighter Tomorrow',
                'hero_headline' => $request->hero_headline ?? 'Lighting for a Better Life',
                'hero_description' => $request->hero_description ?? 'High-quality, energy-efficient and beautifully designed lighting solutions.',
                'facebook_url' => $request->facebook_url ?? '#',
                'instagram_url' => $request->instagram_url ?? '#',
                'youtube_url' => $request->youtube_url ?? '#',
                'linkedin_url' => $request->linkedin_url ?? '#',
            ];

            // Ensure destination directory exists in public/uploads/frontend
            $uploadDir = public_path('uploads/frontend');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0777, true, true);
            }

            // Handle Logo Upload
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $logoName = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
                
                // Remove old logo file if exists
                if ($setting->logo_path && File::exists(public_path($setting->logo_path))) {
                    File::delete(public_path($setting->logo_path));
                }

                $logoFile->move($uploadDir, $logoName);
                $data['logo_path'] = 'uploads/frontend/' . $logoName;
                Log::info('New Frontend Logo uploaded to: ' . $data['logo_path']);
            }

            // Handle Catalogue PDF Upload
            if ($request->hasFile('catalogue_pdf')) {
                $pdfFile = $request->file('catalogue_pdf');
                $pdfName = 'catalogue_' . time() . '.' . $pdfFile->getClientOriginalExtension();

                // Remove old catalogue PDF file if exists
                if ($setting->catalogue_pdf_path && File::exists(public_path($setting->catalogue_pdf_path))) {
                    File::delete(public_path($setting->catalogue_pdf_path));
                }

                $pdfFile->move($uploadDir, $pdfName);
                $data['catalogue_pdf_path'] = 'uploads/frontend/' . $pdfName;
                Log::info('New Frontend Catalogue PDF uploaded to: ' . $data['catalogue_pdf_path']);
            }

            // Handle file removals
            if ($request->has('remove_logo') && $request->remove_logo == '1') {
                if ($setting->logo_path && File::exists(public_path($setting->logo_path))) {
                    File::delete(public_path($setting->logo_path));
                }
                $data['logo_path'] = null;
            }

            if ($request->has('remove_catalogue_pdf') && $request->remove_catalogue_pdf == '1') {
                if ($setting->catalogue_pdf_path && File::exists(public_path($setting->catalogue_pdf_path))) {
                    File::delete(public_path($setting->catalogue_pdf_path));
                }
                $data['catalogue_pdf_path'] = null;
            }

            $setting->fill($data);
            $setting->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Frontend Website Settings updated successfully!',
                    'data' => $setting
                ]);
            }

            return redirect()->back()->with('success', 'Frontend Website Settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error saving frontend settings: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
