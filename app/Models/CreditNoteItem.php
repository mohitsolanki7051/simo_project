<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $collection = 'credit_note_items';

    protected $fillable = [
        'credit_note_id',
        'sales_return_item_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'price',
        'tax_percent',
        'tax_amount',
        'discount_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }

    public function salesReturnItem()
    {
        return $this->belongsTo(SalesReturnItem::class, 'sales_return_item_id');
    }

    public function product()
    {
        if ($this->variant_id) {
            return $this->belongsTo(VariantProduct::class, 'product_id');
        }
        return $this->belongsTo(SimpleProduct::class, 'product_id');
    }

    // Decimal128 to float helper
    public function getQuantityFloatAttribute()
    {
        return $this->decimalToFloat($this->quantity);
    }

    public function getPriceFloatAttribute()
    {
        return $this->decimalToFloat($this->price);
    }

    public function getTaxAmountFloatAttribute()
    {
        return $this->decimalToFloat($this->tax_amount);
    }

    public function getDiscountAmountFloatAttribute()
    {
        return $this->decimalToFloat($this->discount_amount);
    }

    public function getTotalFloatAttribute()
    {
        return $this->decimalToFloat($this->total);
    }

    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
