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
        'party_id',
        'quotation_date',
        'valid_till',
        'subtotal',
        'discount_amount',
        'extra_discount',
        'extra_discount_type',
        'extra_charge',
        'charge_name',
        'round_off',
        'grand_total',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_till' => 'date',
        // Don't cast decimal fields here - we'll handle in accessors
    ];

    // Relationships
    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
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
    public function getSubtotalAttribute($value)
    {
        return $this->convertDecimalToFloat($value);
    }

    public function getDiscountAmountAttribute($value)
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

    // Helper method to convert Decimal128 to float
    private function convertDecimalToFloat($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'badge-secondary',
            'sent' => 'badge-primary',
            'accepted' => 'badge-success',
            'rejected' => 'badge-danger',
            'expired' => 'badge-warning'
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst($this->status);
    }

    // Helper to check if expired
    public function isExpired()
    {
        return $this->valid_till && $this->valid_till->isPast();
    }
}
