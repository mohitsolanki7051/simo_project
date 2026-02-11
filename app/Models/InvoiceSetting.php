<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $collection = 'invoice_settings';

    protected $fillable = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'gstin',
        'pan',
        'logo_path',
        'signature_path',
        'stamp_path',
        'bank_name',
        'account_name',
        'account_number',
        'ifsc_code',
        'branch',
        'terms_and_conditions',
        'footer_note'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Get active settings (only one record should be active)
    public static function getActive()
    {
        return self::first();
    }

    // Accessor for logo URL
    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    // Accessor for signature URL
    public function getSignatureUrlAttribute()
    {
        return $this->signature_path ? asset('storage/' . $this->signature_path) : null;
    }

    // Accessor for stamp URL
    public function getStampUrlAttribute()
    {
        return $this->stamp_path ? asset('storage/' . $this->stamp_path) : null;
    }
}
