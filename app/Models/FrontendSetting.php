<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class FrontendSetting extends Model
{
    use HasFactory;

    protected $collection = 'frontend_settings';

    protected $fillable = [
        'company_name',
        'brand_tagline',
        'phone_number',
        'email_address',
        'location_address',
        'working_hours',
        'hero_subtitle',
        'hero_headline',
        'hero_description',
        'logo_path',
        'catalogue_pdf_path',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'linkedin_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get active frontend settings (singleton record)
     */
    public static function getActive()
    {
        $setting = self::first();
        if (!$setting) {
            $setting = new self([
                'company_name' => 'Simko',
                'brand_tagline' => 'Light | Smart | Innovation',
                'phone_number' => '+91 98765 43210',
                'email_address' => 'info@simkolights.com',
                'location_address' => 'Jaipur, Rajasthan, India',
                'working_hours' => 'Mon - Sat: 9:00 AM - 6:00 PM',
                'hero_subtitle' => 'Illuminate a Brighter Tomorrow',
                'hero_headline' => 'Lighting for a Better Life',
                'hero_description' => 'High-quality, energy-efficient and beautifully designed lighting solutions for homes, offices and modern spaces.',
                'facebook_url' => '#',
                'instagram_url' => '#',
                'youtube_url' => '#',
                'linkedin_url' => '#',
            ]);
        }
        return $setting;
    }

    /**
     * Accessor for Logo URL
     */
    public function getLogoUrlAttribute()
    {
        if (!empty($this->logo_path)) {
            if (str_starts_with($this->logo_path, 'http')) {
                return $this->logo_path;
            }
            if (file_exists(public_path($this->logo_path))) {
                return asset($this->logo_path);
            }
            if (\Storage::disk('public')->exists($this->logo_path)) {
                return asset('storage/' . $this->logo_path);
            }
            return asset($this->logo_path);
        }
        return null;
    }

    /**
     * Accessor for Catalogue PDF URL
     */
    public function getCataloguePdfUrlAttribute()
    {
        if (!empty($this->catalogue_pdf_path)) {
            if (str_starts_with($this->catalogue_pdf_path, 'http')) {
                return $this->catalogue_pdf_path;
            }
            if (file_exists(public_path($this->catalogue_pdf_path))) {
                return asset($this->catalogue_pdf_path);
            }
            if (\Storage::disk('public')->exists($this->catalogue_pdf_path)) {
                return asset('storage/' . $this->catalogue_pdf_path);
            }
            return asset($this->catalogue_pdf_path);
        }
        return null;
    }
}
