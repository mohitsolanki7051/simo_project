<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_type',        // 'simple' or 'variant'
        'variant_id',          // For variant products (nullable)
        'warehouse_id',        // Optional - for future warehouse tracking
        'adjustment_type',     // 'add', 'reduce', 'set'
        'old_quantity',
        'new_quantity',
        'adjustment_quantity',
        'date',
        'remarks',
        'adjusted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'adjustment_quantity' => 'integer',
    ];

    /**
     * Get the product (can be SimpleProduct or VariantProduct)
     */
    public function getProductAttribute()
    {
        if ($this->product_type === 'simple') {
            return SimpleProduct::find($this->product_id);
        } elseif ($this->product_type === 'variant') {
            return VariantProduct::find($this->product_id);
        }
        return null;
    }

    /**
     * Get the warehouse (optional)
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the user who adjusted the stock
     */
    public function adjustedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'adjusted_by');
    }

    /**
     * Get product name with variant (if applicable)
     */
    public function getProductNameAttribute()
    {
        $product = $this->product;

        if (!$product) {
            return 'Unknown Product';
        }

        $name = $product->name;

        // If variant product and variant_id exists, append variant name
        if ($this->product_type === 'variant' && $this->variant_id) {
            if (isset($product->variants) && is_array($product->variants)) {
                foreach ($product->variants as $variant) {
                    if (isset($variant['_id']) && $variant['_id'] == $this->variant_id) {
                        $name .= ' - ' . ($variant['name'] ?? 'Variant');
                        break;
                    }
                }
            }
        }

        return $name;
    }

    /**
     * Scope: Recent stock changes
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope: For specific product
     */
    public function scopeForProduct($query, $productId, $productType = null)
    {
        $query->where('product_id', $productId);

        if ($productType) {
            $query->where('product_type', $productType);
        }

        return $query;
    }

    /**
     * Scope: For specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Get formatted adjustment type
     */
    public function getFormattedAdjustmentTypeAttribute()
    {
        return match($this->adjustment_type) {
            'add' => 'Stock Added',
            'reduce' => 'Stock Reduced',
            'set' => 'Stock Set',
            default => ucfirst($this->adjustment_type)
        };
    }

    /**
     * Get adjustment with sign
     */
    public function getAdjustmentWithSignAttribute()
    {
        return match($this->adjustment_type) {
            'add' => '+' . $this->adjustment_quantity,
            'reduce' => '-' . $this->adjustment_quantity,
            'set' => '=' . $this->new_quantity,
            default => $this->adjustment_quantity
        };
    }
}
