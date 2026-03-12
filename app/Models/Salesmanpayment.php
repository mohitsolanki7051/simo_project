<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SalesmanPayment extends Model
{
    use HasFactory;

    protected $collection = 'salesman_payments';

    protected $fillable = [
        'salesman_id',
        'payment_type',       // 'fixed' | 'commission'
        'amount',             // actual amount paid
        'full_amount',        // total that was owed (for partial tracking)
        'is_partial',         // boolean: true if partial payment
        'period_type',        // '1month' | '6months' | '1year' | 'custom'
        'period_start',
        'period_end',
        'sales_total',
        'payment_method',     // 'cash' | 'upi' | 'bank_transfer' | 'cheque'
        'transaction_id',
        'notes',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'amount'       => 'float',
        'full_amount'  => 'float',
        'sales_total'  => 'float',
        'is_partial'   => 'boolean',
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
    ];

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'Cash',
            'upi'           => 'UPI',
            'bank_transfer' => 'Bank Transfer',
            'cheque'        => 'Cheque',
            default         => ucfirst($this->payment_method ?? ''),
        };
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        if ($this->is_partial) {
            return 'Partial';
        }
        return 'Full';
    }
}
