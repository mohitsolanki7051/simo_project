<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $collection = 'sales_return_items';

    protected $fillable = [
        'sales_return_id',
        'sales_invoice_item_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'price',
        'tax_percent',
        'tax_amount',
        'subtotal',           // ✅ New field
        'total',               // ✅ subtotal + tax
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',    // ✅ New cast
        'total' => 'decimal:2',
    ];

    // Relationships
    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(SalesInvoiceItem::class, 'sales_invoice_item_id');
    }

    public function product()
    {
        if ($this->variant_id) {
            return $this->belongsTo(VariantProduct::class, 'product_id');
        }
        return $this->belongsTo(SimpleProduct::class, 'product_id');
    }

    // Accessors
    public function getTaxAmountAttribute($value)
    {
        return $this->decimalToFloat($value);
    }

    public function getSubtotalAttribute($value)
    {
        return $this->decimalToFloat($value);
    }

    public function getTotalAttribute($value)
    {
        return $this->decimalToFloat($value);
    }

    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
