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
        'vendor_id',
        'amount',
        'payment_method',
        'payment_date',
        'status',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
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

    // Accessors
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
