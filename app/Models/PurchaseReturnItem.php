<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory;

    protected $collection = 'purchase_return_items';

    protected $fillable = [
        'purchase_return_id',
        'purchase_invoice_item_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_name',
        'quantity',
        'price',
        'tax_percent',
        'tax_amount',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
        'tax_percent' => 'float',
        'tax_amount' => 'float',
        'subtotal' => 'float',
        'total' => 'float',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    public function purchaseInvoiceItem()
    {
        return $this->belongsTo(PurchaseInvoiceItem::class, 'purchase_invoice_item_id');
    }

    public function product()
    {
        if ($this->variant_id) {
            return $this->belongsTo(VariantProduct::class, 'product_id');
        }
        return $this->belongsTo(SimpleProduct::class, 'product_id');
    }

    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
