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
        'salesman_id',
        'party_type',        // customer | dealer | distributor
        'opening_balance',   // NEW
        'credit_limit',      // NEW
        'parent_party_id',   // NEW (dealer → distributor)
        'gst_number',
        'pan_number',
        'status',
        'notes',
        'advance_balance', 
    ];

    protected $casts = [
        'status' => 'string',
        'opening_balance' => 'float',
        'credit_limit' => 'float',
    ];

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

    // Parent party relationship (for dealers under distributors)
    public function parentParty()
    {
        return $this->belongsTo(Customer::class, 'parent_party_id');
    }

    // Child parties relationship
    public function childParties()
    {
        return $this->hasMany(Customer::class, 'parent_party_id');
    }

    // Scopes for party types
    public function scopeCustomers($query)
    {
        return $query->where('party_type', 'customer');
    }

    public function scopeDealers($query)
    {
        return $query->where('party_type', 'dealer');
    }

    public function scopeDistributors($query)
    {
        return $query->where('party_type', 'distributor');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Accessors
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    public function getPartyTypeTextAttribute()
    {
        return ucfirst($this->party_type);
    }

    public function getFormattedOpeningBalanceAttribute()
    {
        return $this->opening_balance ? number_format($this->opening_balance, 2) : '0.00';
    }

    public function getFormattedCreditLimitAttribute()
    {
        return $this->credit_limit ? number_format($this->credit_limit, 2) : '0.00';
    }
    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }
}
