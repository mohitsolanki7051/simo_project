<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'name', // contact persons
        'phone',
        'email',
        'gst_number',
        'pan_number',
        'opening_balance',
        'credit_limit',
        'bank_name',
        'account_number',
        'ifsc_code',
        'account_holder_name',
        'purchase_executive_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'credit_limit' => 'float',
    ];

    // Relationships
    public function addresses()
    {
        return $this->hasMany(VendorAddress::class);
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

    public function purchaseExecutive()
    {
        return $this->belongsTo(Salesman::class, 'purchase_executive_id');
    }

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'vendor_id');
    }

    // Scopes
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

    public function getFormattedOpeningBalanceAttribute()
    {
        return '₹' . number_format($this->opening_balance, 2);
    }

    public function getFormattedCreditLimitAttribute()
    {
        return $this->credit_limit ? '₹' . number_format($this->credit_limit, 2) : 'No Limit';
    }

    public function getDisplayNameAttribute()
    {
        return $this->company_name . ($this->name ? ' (' . $this->name . ')' : '');
    }

    public function getBankInfoAttribute()
    {
        if (!$this->bank_name && !$this->account_number) {
            return null;
        }

        $info = [];
        if ($this->account_holder_name) $info[] = $this->account_holder_name;
        if ($this->bank_name) $info[] = $this->bank_name;
        if ($this->account_number) $info[] = 'A/C: ' . $this->account_number;
        if ($this->ifsc_code) $info[] = 'IFSC: ' . $this->ifsc_code;

        return implode(' | ', $info);
    }
}
