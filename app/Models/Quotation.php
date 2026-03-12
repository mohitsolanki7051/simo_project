<?php
// app/Models/Quotation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\Decimal128;

class Quotation extends Model
{
    use HasFactory;

    protected $table = 'quotations';

    protected $fillable = [
        'quotation_number',
        'public_token',
        'invoice_type',       // 'gst' or 'cash'
        'party_id',
        'salesman_id',
        'warehouse_id',
        'quotation_date',
        'valid_till',
        'billing_address',
        'shipping_address',
        'total_mrp',
        'subtotal',
        'discount_amount',
        'tax_total',
        'cgst_total',
        'sgst_total',
        'igst_total',
        'tax_type',           // 'intra' or 'inter'
        'extra_discount',
        'extra_discount_type',
        'extra_charge',
        'charge_name',
        'round_off',
        'grand_total',
        'notes',
        'status',             // 'draft', 'sent', 'accepted', 'rejected', 'expired'
        'converted_to_invoice', // true/false - set to true when converted to sales invoice
        'created_by',
    ];

    protected $casts = [
        'quotation_date'       => 'date',
        'valid_till'           => 'date',
        'converted_to_invoice' => 'boolean',
    ];

    // Relationships
    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Accessors to handle Decimal128 conversion
    public function getTotalMrpAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getSubtotalAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getDiscountAmountAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getTaxTotalAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getCgstTotalAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getSgstTotalAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getIgstTotalAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getExtraDiscountAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getExtraChargeAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getRoundOffAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getGrandTotalAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    private function convertDecimalToFloat($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    // Status helpers
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft'    => 'badge-secondary',
            'sent'     => 'badge-primary',
            'accepted' => 'badge-success',
            'rejected' => 'badge-danger',
            'expired'  => 'badge-warning'
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst($this->status);
    }

    public function isExpired()
    {
        return $this->valid_till && $this->valid_till->isPast();
    }

    public function isGstQuotation()
    {
        return $this->invoice_type === 'gst';
    }

    public function isCashMemo()
    {
        return $this->invoice_type === 'cash';
    }

    public function isIntraState()
    {
        return $this->tax_type === 'intra';
    }

    public function isInterState()
    {
        return $this->tax_type === 'inter';
    }

    /**
     * Check if quotation has been converted to invoice
     */
    public function isConvertedToInvoice()
    {
        return (bool) ($this->converted_to_invoice ?? false);
    }
}
