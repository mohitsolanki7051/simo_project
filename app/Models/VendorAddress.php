<?php
// app/Models/VendorAddress.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class VendorAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'type',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'landmark',
        'contact_person',
        'contact_number',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        $parts = [
            $this->address,
            $this->landmark,
            $this->city,
            $this->state,
            $this->pincode,
            $this->country
        ];

        return implode(', ', array_filter($parts));
    }

    public function getAddressTypeTextAttribute()
    {
        return ucfirst($this->type);
    }
}
