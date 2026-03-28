<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;

    protected $collection = 'credit_notes';

    protected $fillable = [
        'credit_note_number',
        'party_id',
        'sales_invoice_id',
        'sales_return_id',
        'credit_date',
        'subtotal',              // ✅ New field
        'tax_amount',             // ✅ New field
        'discount_amount',        // ✅ New field
        'amount',
        'used_amount',
        'remaining_amount',
        'reason',
        'status',
        'created_by',
    ];

    protected $casts = [
        'credit_date' => 'date',
        'subtotal' => 'decimal:2',          // ✅ New cast
        'tax_amount' => 'decimal:2',         // ✅ New cast
        'discount_amount' => 'decimal:2',    // ✅ New cast
        'amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Relationships
    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }

    public function items()
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'badge-success',
            'partial'  => 'badge-warning',
            'settled'  => 'badge-info',
        ];
        return $badges[$this->status] ?? 'badge-secondary';
    }

    // Helper: Can be used for adjustment?
    public function canBeUsed()
    {
          return in_array($this->status, ['active', 'partial'])
           && $this->remaining_amount > 0;
    }

    // Helper: Get available amount for adjustment
    public function getAvailableAmountAttribute()
    {
        return $this->remaining_amount ?? 0;
    }

    // Helper: Apply adjustment (partial or full)
    public function applyAdjustment($adjustAmount)
    {
        if ($adjustAmount <= 0) {
            throw new \Exception('Adjustment amount must be greater than 0');
        }

        if ($adjustAmount > $this->remaining_amount) {
            throw new \Exception('Adjustment amount exceeds remaining credit');
        }

        $this->used_amount += $adjustAmount;
        $this->remaining_amount -= $adjustAmount;

        if ($this->remaining_amount == $this->amount) {
            $this->status = 'active';
        } elseif ($this->remaining_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'settled';
        }

        $this->save();

        return $this;
    }

    // Decimal128 to float helpers
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

    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
