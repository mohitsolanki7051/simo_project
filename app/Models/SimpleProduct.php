<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SimpleProduct extends Model
{
    use HasFactory;

    protected $collection = 'simple_products';

    protected $fillable = [
        'type',
        'name',
        'category_id',
        'brand',
        'body_type',
        'warranty_duration',
        'warranty_unit',
        'status',

        'sku_code',
        'barcode',
        'barcode_symbology',

        'cost_price',
        'sale_price',
        'mrp_price',
        'dealer_price',
        'distributor_price',

        'hsn_code',
        'gst',
        'unit',

        'base_image',
        'gallery_images',
        'description',
        'cost_history',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'cost_history' => 'array',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'mrp_price' => 'decimal:2',
        'dealer_price' => 'decimal:2',
        'distributor_price' => 'decimal:2',
        'gst' => 'decimal:2',
        'warranty_duration' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
      public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // 🔹 Total stock accessor (SUM of all warehouses)
    public function getTotalStockAttribute()
    {
        return WarehouseStock::where('product_id', $this->_id)->sum('quantity');
    }

    public function getCurrentStockAttribute()
    {
        return $this->total_stock;
    }

    public function hasVariants()
    {
        return false;
    }
    public function getMainWarehouseStockAttribute()
    {
        try {
            // Find main warehouse
            $mainWarehouse = Warehouse::main()->first();
            if (!$mainWarehouse) {
                return 0;
            }

            // Get stock from warehouse_stocks table
            $warehouseStock = WarehouseStock::where('product_id', $this->_id)
                ->where('product_type', 'simple')
                ->where('warehouse_id', $mainWarehouse->id)
                ->first();

            return $warehouseStock ? $warehouseStock->quantity : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get stock from specific warehouse
     */
    public function getStockByWarehouse($warehouseId)
    {
        try {
            $warehouseStock = WarehouseStock::where('product_id', $this->_id)
                ->where('product_type', 'simple')
                ->where('warehouse_id', $warehouseId)
                ->first();

            return $warehouseStock ? $warehouseStock->quantity : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get min stock alert from main warehouse
     */
    public function getMainWarehouseMinStockAlertAttribute()
    {
        try {
            $mainWarehouse = Warehouse::main()->first();
            if (!$mainWarehouse) {
                return 0;
            }

            $warehouseStock = WarehouseStock::where('product_id', $this->_id)
                ->where('product_type', 'simple')
                ->where('warehouse_id', $mainWarehouse->id)
                ->first();

            return $warehouseStock ? $warehouseStock->min_stock_alert : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    public function getCostPriceAtDate($dateStr)
    {
        $costHistory = $this->cost_history ?? [];
        if (empty($costHistory)) {
            return $this->convertToFloat($this->cost_price ?? 0);
        }

        // Sort cost history by date ASC
        usort($costHistory, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        // Find the closest cost price where entry date <= target date
        $lastCost = null;
        foreach ($costHistory as $entry) {
            if (strcmp($entry['date'], $dateStr) <= 0) {
                $lastCost = $this->convertToFloat($entry['cost_price']);
            } else {
                break;
            }
        }

        return $lastCost !== null ? $lastCost : $this->convertToFloat($this->cost_price ?? 0);
    }

    private function convertToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'product_id');
    }
}

