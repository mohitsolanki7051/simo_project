<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $collection = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'type',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'landmark',
        'is_default',
        'contact_person',
        'contact_number'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
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
