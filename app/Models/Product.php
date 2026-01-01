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
        'brand_id',
        'body_type',
        'warranty',
        'status',

        // Pricing & Tax
        'mrp_price',
        'price',
        'hsn_code',
        'gst',

        // Variants
        'sku_code',
        'watt',
        'shape',
        'color_temperature',
        'cutting_size',
        'unit',

        // Pricing Variants
        'cost_price',
        'dealer_price',
        'distributor_price',

        // Stock Management
        'opening_stock',
        'current_stock',
        'min_stock_alert',

        // Images
        'base_image',
        'gallery_images',

        // Description
        'short_description',
        'description',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'price' => 'decimal:2',
        'mrp_price' => 'decimal:2',
        'gst' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'dealer_price' => 'decimal:2',
        'distributor_price' => 'decimal:2',
        'opening_stock' => 'integer',
        'current_stock' => 'integer',
        'min_stock_alert' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    public function getFormattedMrpPriceAttribute()
    {
        return '₹' . number_format($this->mrp_price, 2);
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->mrp_price && $this->price < $this->mrp_price) {
            return round((($this->mrp_price - $this->price) / $this->mrp_price) * 100);
        }
        return 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->current_stock <= $this->min_stock_alert) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getIsLowStockAttribute()
    {
        return $this->current_stock > 0 && $this->current_stock <= $this->min_stock_alert;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->current_stock <= 0;
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
        return $query->whereColumn('current_stock', '<=', 'min_stock_alert')
                     ->where('current_stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    // Methods
    public function updateStock($quantity, $type = 'add')
    {
        if ($type === 'add') {
            $this->current_stock += $quantity;
        } elseif ($type === 'subtract') {
            $this->current_stock = max(0, $this->current_stock - $quantity);
        } elseif ($type === 'set') {
            $this->current_stock = max(0, $quantity);
        }

        $this->save();
        return $this->current_stock;
    }

    public function calculateFinalPrice()
    {
        $basePrice = $this->price;
        $gstAmount = ($basePrice * $this->gst) / 100;
        return $basePrice + $gstAmount;
    }

    public function calculateProfit($type = 'selling')
    {
        if (!$this->cost_price) {
            return 0;
        }

        $sellingPrice = match($type) {
            'dealer' => $this->dealer_price,
            'distributor' => $this->distributor_price,
            default => $this->price,
        };

        return $sellingPrice - $this->cost_price;
    }

    public function calculateProfitMargin($type = 'selling')
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return 0;
        }

        $profit = $this->calculateProfit($type);
        return round(($profit / $this->cost_price) * 100, 2);
    }
}
