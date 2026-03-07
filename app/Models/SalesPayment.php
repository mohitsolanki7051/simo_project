<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SalesPayment extends Model
{
    use HasFactory;

    protected $collection = 'sales_payments';

    protected $fillable = [
        'payment_number',
        'party_id',
        'sales_invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'status',
        'reference_no',
        'notes',
        'payment_type',
        'allocations'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'allocations' => 'array'
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
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
            'other' => 'Other'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }
}
