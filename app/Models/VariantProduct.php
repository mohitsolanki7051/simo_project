<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class VariantProduct extends Model
{
    use HasFactory;

    protected $collection = 'variant_products';

    protected $fillable = [
        'name',
        'category_id',
        'brand',
        'warranty_duration',
        'warranty_unit',
        'status',
        'hsn_code',
        'gst',
        'base_image',
        'gallery_images',
        'description',
        'variants',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'variants' => 'array',
        'gst' => 'float',
        'warranty_duration' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function getVariantsAttribute($value)
{
    if (is_string($value)) {
        try {
            $decoded = json_decode($value, true);

            // Convert _id strings back to proper format
            if (is_array($decoded)) {
                foreach ($decoded as &$variant) {
                    if (isset($variant['_id'])) {
                        // If _id is already an array (MongoDB format), convert it to string
                        if (is_array($variant['_id']) && isset($variant['_id']['$oid'])) {
                            $variant['_id'] = $variant['_id']['$oid'];
                        }
                        // If _id is ObjectId, convert to string
                        elseif ($variant['_id'] instanceof \MongoDB\BSON\ObjectId) {
                            $variant['_id'] = (string) $variant['_id'];
                        }
                    }
                }
                return $decoded;
            }
            return [];
        } catch (\Exception $e) {
            Log::error('Error decoding variants: ' . $e->getMessage());
            return [];
        }
    }

    // If value is already array, ensure _id is string
    if (is_array($value)) {
        foreach ($value as &$variant) {
            if (isset($variant['_id'])) {
                // Convert ObjectId to string if needed
                if ($variant['_id'] instanceof \MongoDB\BSON\ObjectId) {
                    $variant['_id'] = (string) $variant['_id'];
                }
            }
        }
    }

    return is_array($value) ? $value : [];
}

    // ✅ FIX: Mutator to store variants as JSON
    public function setVariantsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['variants'] = json_encode($value, JSON_UNESCAPED_SLASHES);
        } else {
            $this->attributes['variants'] = $value;
        }
    }
    // ✅ WarehouseStocks relationship (वेयरहाउस स्टॉक्स के लिए)
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'product_id');
    }

    // ✅ Get main warehouse stock
    public function getMainWarehouseStock()
    {
        $mainWarehouse = Warehouse::main()->first();
        if (!$mainWarehouse) {
            return collect();
        }

        return WarehouseStock::where('product_id', $this->_id)
            ->where('warehouse_id', $mainWarehouse->id)
            ->get();
    }

    // ✅ Get total stock from all warehouses
    public function getTotalStockAttribute()
    {
        return WarehouseStock::where('product_id', $this->_id)->sum('quantity');
    }

    // ✅ Get stock status
    public function getStockStatusAttribute()
    {
        $totalStock = $this->total_stock;

        if ($totalStock <= 0) {
            return 'out_of_stock';
        } else {
            return 'in_stock';
        }
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock_status === 'out_of_stock';
    }

    // Helper method to convert MongoDB Decimal128 to float
    private function convertToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    // Accessors
    public function getMinPriceAttribute()
    {
        if (!$this->variants || !is_array($this->variants) || count($this->variants) === 0) {
            return 0;
        }

        $prices = [];
        foreach ($this->variants as $variant) {
            if (isset($variant['sale_price'])) {
                $prices[] = $this->convertToFloat($variant['sale_price']);
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
            if (isset($variant['sale_price'])) {
                $prices[] = $this->convertToFloat($variant['sale_price']);
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
            return '₹' . number_format($min, 2);
        }

        return '₹' . number_format($min, 2) . ' - ₹' . number_format($max, 2);
    }

    public function getFormattedMrpPriceRangeAttribute()
    {
        $min = $this->min_mrp_price;
        $max = $this->max_mrp_price;

        if ($min == 0 && $max == 0) {
            return '₹0';
        }

        if ($min == $max) {
            return '₹' . number_format($min, 2);
        }

        return '₹' . number_format($min, 2) . ' - ₹' . number_format($max, 2);
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

    // Check if product has variants
    public function hasVariants()
    {
        return !empty($this->variants) && is_array($this->variants) && count($this->variants) > 0;
    }

    // Get all SKUs from variants
    public function getAllSkus()
    {
        $skus = [];

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
            if (isset($variant['sku_code']) && $variant['sku_code'] === $sku) {
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

    public function getFormattedWarrantyAttribute()
    {
        if (!$this->warranty_duration || !$this->warranty_unit) {
            return 'No warranty';
        }

        $unit = $this->warranty_unit === 'year' ? 'year(s)' : 'month(s)';
        return $this->warranty_duration . ' ' . $unit;
    }
      public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    // App\Models\VariantProduct.php में add करें
public function getVariantStock($variantId)
{
    $mainWarehouse = Warehouse::main()->first();
    if (!$mainWarehouse) {
        return 0;
    }

    $stock = WarehouseStock::where('product_id', $this->_id)
        ->where('variant_id', $variantId)
        ->where('warehouse_id', $mainWarehouse->id)
        ->first();

    return $stock ? $stock->quantity : 0;
}
 public function getVariantMainWarehouseStock($variantId)
    {
        try {
            $mainWarehouse = Warehouse::main()->first();
            if (!$mainWarehouse) {
                return 0;
            }

            $warehouseStock = WarehouseStock::where('product_id', $this->_id)
                ->where('product_type', 'variant')
                ->where('variant_id', (string) $variantId)
                ->where('warehouse_id', $mainWarehouse->id)
                ->first();

            return $warehouseStock ? $warehouseStock->quantity : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get min stock alert for a variant from main warehouse
     */
    public function getVariantMinStockAlert($variantId)
    {
        try {
            $mainWarehouse = Warehouse::main()->first();
            if (!$mainWarehouse) {
                return 0;
            }

            $warehouseStock = WarehouseStock::where('product_id', $this->_id)
                ->where('product_type', 'variant')
                ->where('variant_id', (string) $variantId)
                ->where('warehouse_id', $mainWarehouse->id)
                ->first();

            return $warehouseStock ? $warehouseStock->min_stock_alert : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get all variants with their main warehouse stock
     */
    public function getVariantsWithMainWarehouseStock()
    {
        try {
            $mainWarehouse = Warehouse::main()->first();
            if (!$mainWarehouse || !is_array($this->variants)) {
                return $this->variants ?? [];
            }

            $variantsWithStock = [];

            foreach ($this->variants as $variant) {
                $variantId = $variant['_id'] ?? null;
                if (!$variantId) {
                    continue;
                }

                // Get stock from warehouse_stocks
                $warehouseStock = WarehouseStock::where('product_id', $this->_id)
                    ->where('product_type', 'variant')
                    ->where('variant_id', (string) $variantId)
                    ->where('warehouse_id', $mainWarehouse->id)
                    ->first();

                $stock = $warehouseStock ? $warehouseStock->quantity : 0;

                $variant['current_stock'] = $stock;
                $variant['min_stock_alert'] = $warehouseStock ? $warehouseStock->min_stock_alert : 0;

                $variantsWithStock[] = $variant;
            }

            return $variantsWithStock;
        } catch (\Exception $e) {
            return $this->variants ?? [];
        }
    }

    public function getVariantCostPriceAtDate($variantId, $dateStr)
    {
        if (!$this->variants || !is_array($this->variants)) {
            return 0;
        }

        foreach ($this->variants as $variant) {
            if ((string)($variant['_id'] ?? '') === (string)$variantId) {
                $costHistory = $variant['cost_history'] ?? [];
                if (empty($costHistory)) {
                    return $this->convertToFloat($variant['cost_price'] ?? 0);
                }

                usort($costHistory, function($a, $b) {
                    return strcmp($a['date'], $b['date']);
                });

                $lastCost = null;
                foreach ($costHistory as $entry) {
                    if (strcmp($entry['date'], $dateStr) <= 0) {
                        $lastCost = $this->convertToFloat($entry['cost_price']);
                    } else {
                        break;
                    }
                }

                return $lastCost !== null ? $lastCost : $this->convertToFloat($variant['cost_price'] ?? 0);
            }
        }

        return 0;
    }
}
