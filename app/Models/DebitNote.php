<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class DebitNote extends Model
{
    use HasFactory;

    protected $collection = 'debit_notes';

    protected $fillable = [
        'debit_note_number',
        'party_id',
        'party_type',
        'purchase_invoice_id',
        'purchase_return_id',
        'debit_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'amount',
        'used_amount',
        'remaining_amount',
        'reason',
        'status',
        'created_by',
    ];

    protected $casts = [
        'debit_date'       => 'date',
        'subtotal'         => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'amount'           => 'decimal:2',
        'used_amount'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function party()
    {
        if ($this->party_type === 'vendor') {
            return $this->belongsTo(\App\Models\Vendor::class, 'party_id');
        }
        return $this->belongsTo(\App\Models\Customer::class, 'party_id');
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }


    public function items()
    {
        return $this->hasMany(DebitNoteItem::class, 'debit_note_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'created_by');
    }

    /* ── Float Accessors ── */

    public function getSubtotalFloatAttribute()
    {
        return $this->decimalToFloat($this->subtotal);
    }

    public function getTaxAmountFloatAttribute()
    {
        return $this->decimalToFloat($this->tax_amount);
    }

    public function getDiscountAmountFloatAttribute()
    {
        return $this->decimalToFloat($this->discount_amount);
    }

    public function getAmountFloatAttribute()
    {
        return $this->decimalToFloat($this->amount);
    }

    public function getUsedAmountFloatAttribute()
    {
        return $this->decimalToFloat($this->used_amount);
    }

    public function getRemainingAmountFloatAttribute()
    {
        return $this->decimalToFloat($this->remaining_amount);
    }

    /* ── Helper ── */

    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
    // In DebitNote model - add these methods

public function getPartyNameAttribute()
{
    if ($this->party_type === 'vendor') {
        $vendor = \App\Models\Vendor::find($this->party_id);
        return $vendor ? $vendor->company_name : 'N/A';
    }

    $customer = \App\Models\Customer::find($this->party_id);
    return $customer ? $customer->name : 'N/A';
}

public function getPartyPhoneAttribute()
{
    if ($this->party_type === 'vendor') {
        $vendor = \App\Models\Vendor::find($this->party_id);
        return $vendor ? ($vendor->phone ?? '-') : '-';
    }

    $customer = \App\Models\Customer::find($this->party_id);
    return $customer ? ($customer->phone ?? '-') : '-';
}
}
