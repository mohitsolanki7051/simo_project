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
        'brand', // Changed from brand_id to brand (text input)
        'body_type', // Changed to text input
        'warranty',
        'status',

        // Main Product SKU & Barcode
        'sku_code',
        'barcode',
        'barcode_symbology',

        // Pricing & Tax
        'mrp_price',
        'price',
        'hsn_code',
        'gst',

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

        // Variants (Array of variant objects)
        'variants',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'variants' => 'array', // Array of variant objects
        'price' => 'decimal:2',
        'mrp_price' => 'decimal:2',
        'gst' => 'decimal:2',
        'opening_stock' => 'integer',
        'current_stock' => 'integer',
        'min_stock_alert' => 'integer',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
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

    // Get total stock including all variants
    public function getTotalStockAttribute()
    {
        $total = $this->current_stock ?? 0;

        if ($this->variants && is_array($this->variants)) {
            foreach ($this->variants as $variant) {
                $total += $variant['current_stock'] ?? 0;
            }
        }

        return $total;
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
}
