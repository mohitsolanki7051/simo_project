<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\Decimal128;

class PaymentInItem extends Model
{
    use HasFactory;

    protected $collection = 'payment_in_items';

    protected $fillable = [
        'payment_in_id',
        'sales_invoice_id',
        'invoice_number',
        'invoice_amount',
        'paid_amount',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'invoice_amount' => 'float',
        'paid_amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(PaymentIn::class, 'payment_in_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    // Accessors for float values
    public function getInvoiceAmountAttribute($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    public function getPaidAmountAttribute($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    public function getBalanceBeforeAttribute($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    public function getBalanceAfterAttribute($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
