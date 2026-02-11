<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $collection = 'customers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'status',
        'gst_number',
        'pan_number',
        'customer_type', // business, individual
        'company_name',
        'notes'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ✅ Add scope for inactive customers
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
    // Relationships

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function billingAddresses()
    {
        return $this->addresses()->where('type', 'billing');
    }

    public function shippingAddresses()
    {
        return $this->addresses()->where('type', 'shipping');
    }

    public function defaultBillingAddress()
    {
        return $this->billingAddresses()->where('is_default', true)->first();
    }

    public function defaultShippingAddress()
    {
        return $this->shippingAddresses()->where('is_default', true)->first();
    }

    // Accessors
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    public function getCustomerTypeTextAttribute()
    {
        return ucfirst($this->customer_type);
    }
}
