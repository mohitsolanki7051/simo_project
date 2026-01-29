<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic Information
        'name',
        'category_id',
        'warehouse_id',
        'brand',
        'body_type',
        'warranty',
        'status',

        // Main Product SKU & Barcode
        'sku_code',
        'barcode',
        'barcode_symbology',

        // Tax (pricing removed from main product, only in variants)
        'hsn_code',
        'gst',

        // Images
        'base_image',
        'gallery_images',

        // Description
        'short_description',
        'description',

        // Variants (Array of variant objects with pricing and stock)
        'variants',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'variants' => 'array',
        'gst' => 'decimal:2',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Helper method to convert MongoDB Decimal128 to float
    private function convertToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    // Accessors - Get pricing from variants (using cost_price as sale price)
    public function getMinPriceAttribute()
    {
        if (!$this->variants || !is_array($this->variants) || count($this->variants) === 0) {
            return 0;
        }

        $prices = [];
        foreach ($this->variants as $variant) {
            if (isset($variant['cost_price'])) {
                $prices[] = $this->convertToFloat($variant['cost_price']);
            }
        }

        return !empty($prices) ? min($prices) : 0;
    }

    public function getMaxPriceAttribute()
    {
        if (!$this->variants || !is_array($this->variants) || count($this->variants) === 0) {
            return 0;
        }

        $prices = [];
        foreach ($this->variants as $variant) {
            if (isset($variant['cost_price'])) {
                $prices[] = $this->convertToFloat($variant['cost_price']);
            }
        }

        return !empty($prices) ? max($prices) : 0;
    }

    public function getMinMrpPriceAttribute()
    {
        if (!$this->variants || !is_array($this->variants) || count($this->variants) === 0) {
            return 0;
        }

        $prices = [];
        foreach ($this->variants as $variant) {
            if (isset($variant['mrp_price'])) {
                $prices[] = $this->convertToFloat($variant['mrp_price']);
            }
        }

        return !empty($prices) ? min($prices) : 0;
    }

    public function getMaxMrpPriceAttribute()
    {
        if (!$this->variants || !is_array($this->variants) || count($this->variants) === 0) {
            return 0;
        }

        $prices = [];
        foreach ($this->variants as $variant) {
            if (isset($variant['mrp_price'])) {
                $prices[] = $this->convertToFloat($variant['mrp_price']);
            }
        }

        return !empty($prices) ? max($prices) : 0;
    }

    public function getFormattedPriceRangeAttribute()
    {
        $min = $this->min_price;
        $max = $this->max_price;

        if ($min == 0 && $max == 0) {
            return '₹0';
        }

        if ($min == $max) {
            return '₹' . number_format($min, 0);
        }

        return '₹' . number_format($min, 0) . ' - ₹' . number_format($max, 0);
    }

    public function getFormattedMrpPriceRangeAttribute()
    {
        $min = $this->min_mrp_price;
        $max = $this->max_mrp_price;

        if ($min == 0 && $max == 0) {
            return '₹0';
        }

        if ($min == $max) {
            return '₹' . number_format($min, 0);
        }

        return '₹' . number_format($min, 0) . ' - ₹' . number_format($max, 0);
    }

    // Get total stock from all variants
    public function getTotalStockAttribute()
    {
        $total = 0;

        if ($this->variants && is_array($this->variants)) {
            foreach ($this->variants as $variant) {
                $total += $variant['current_stock'] ?? 0;
            }
        }

        return $total;
    }

    // Get stock status based on all variants
    public function getStockStatusAttribute()
    {
        $totalStock = $this->total_stock;
        $minAlert = 0;

        if ($this->variants && is_array($this->variants)) {
            $minAlerts = array_column($this->variants, 'min_stock_alert');
            $minAlert = !empty($minAlerts) ? min($minAlerts) : 0;
        }

        if ($totalStock <= 0) {
            return 'out_of_stock';
        } elseif ($totalStock <= $minAlert) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getIsLowStockAttribute()
    {
        return $this->stock_status === 'low_stock';
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock_status === 'out_of_stock';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeLowStock($query)
    {
        // Check if any variant has low stock
        return $query->where(function($q) {
            $q->whereRaw('variants.current_stock <= variants.min_stock_alert')
              ->where('variants.current_stock', '>', 0);
        });
    }

    public function scopeOutOfStock($query)
    {
        // Check if all variants are out of stock
        return $query->where(function($q) {
            $q->whereRaw('variants.current_stock <= 0');
        });
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        if ($warehouseId) {
            return $query->where('warehouse_id', $warehouseId);
        }
        return $query;
    }

    // Methods
    public function updateVariantStock($variantIndex, $quantity, $type = 'add')
    {
        if (!isset($this->variants[$variantIndex])) {
            return false;
        }

        $variants = $this->variants;
        $currentStock = $variants[$variantIndex]['current_stock'] ?? 0;

        if ($type === 'add') {
            $variants[$variantIndex]['current_stock'] = $currentStock + $quantity;
        } elseif ($type === 'subtract') {
            $variants[$variantIndex]['current_stock'] = max(0, $currentStock - $quantity);
        } elseif ($type === 'set') {
            $variants[$variantIndex]['current_stock'] = max(0, $quantity);
        }

        $this->variants = $variants;
        $this->save();

        return $variants[$variantIndex]['current_stock'];
    }

    public function calculateVariantFinalPrice($variantIndex)
    {
        if (!isset($this->variants[$variantIndex])) {
            return 0;
        }

        $variant = $this->variants[$variantIndex];
        $basePrice = $this->convertToFloat($variant['cost_price'] ?? 0);
        $gstAmount = ($basePrice * $this->convertToFloat($this->gst)) / 100;

        return $basePrice + $gstAmount;
    }

    // Get all SKUs (main + variants)
    public function getAllSkus()
    {
        $skus = [$this->sku_code];

        if ($this->variants && is_array($this->variants)) {
            foreach ($this->variants as $variant) {
                if (!empty($variant['sku_code'])) {
                    $skus[] = $variant['sku_code'];
                }
            }
        }

        return array_filter($skus);
    }

    // Get variant by SKU
    public function getVariantBySku($sku)
    {
        if (!$this->variants || !is_array($this->variants)) {
            return null;
        }

        foreach ($this->variants as $index => $variant) {
            if ($variant['sku_code'] === $sku) {
                return [
                    'index' => $index,
                    'data' => $variant
                ];
            }
        }

        return null;
    }

    // Get all variant names
    public function getVariantNamesAttribute()
    {
        if (!$this->variants || !is_array($this->variants)) {
            return [];
        }

        return array_column($this->variants, 'name');
    }

    // Check if product has variants
    public function hasVariants()
    {
        return !empty($this->variants) && is_array($this->variants) && count($this->variants) > 0;
    }
}
