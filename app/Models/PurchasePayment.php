<?php
// app/Models/PurchasePayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;

    protected $collection = 'purchase_payments';

    protected $fillable = [
        'payment_number',
        'purchase_invoice_id',
        'party_id',
        'amount',
        'payment_method',
        'payment_date',
        'status',
        'reference_no',
        'notes',
        'created_by',
        'payment_type',      // Add this
        'payment_subtype',   // Add this (for purchase_payment or credit_refund)
        'allocations',       // Add this (for storing allocation details)
        'party_details',     // Optional: for caching party info
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
        'allocations' => 'array',  // Cast allocations to array
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Accessor for party (handles both vendor and customer)
    public function getPartyAttribute()
    {
        if (empty($this->party_id)) {
            return null;
        }

        // Try vendor first
        $vendor = Vendor::find($this->party_id);
        if ($vendor) {
            return $vendor;
        }

        // Then try customer
        $customer = Customer::find($this->party_id);
        if ($customer) {
            return $customer;
        }

        return null;
    }

    public function getPaymentMethodTextAttribute()
    {
        $methods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'upi' => 'UPI',
            'credit' => 'Credit'
        ];

        return $methods[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'completed' => 'badge-success',
            'pending' => 'badge-warning',
            'failed' => 'badge-danger',
            'cancelled' => 'badge-secondary'
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }
}
