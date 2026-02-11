<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_name',
        'variant_index',
        'variant_name',
        'sku_code',
        'unit',
        'qty',
        'rate',
        'tax_percent',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'qty' => 'integer',
        'rate' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFormattedTotalAttribute()
    {
        return '₹' . number_format($this->total, 2);
    }

    public function getFormattedRateAttribute()
    {
        return '₹' . number_format($this->rate, 2);
    }

    // Methods
    public function calculateTotal()
    {
        $subtotal = $this->qty * $this->rate;
        $this->tax_amount = ($subtotal * $this->tax_percent) / 100;
        $this->total = $subtotal + $this->tax_amount;
        $this->save();
    }

    public function updateProductStock()
    {
        $product = $this->product;

        if (!$product) {
            return;
        }

        // Update variant stock
        if ($product->variants && isset($product->variants[$this->variant_index])) {
            $variants = $product->variants;
            $variants[$this->variant_index]['current_stock'] += $this->qty;
            $product->variants = $variants;
            $product->save();
        }
    }
}
