<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $collection = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'status'
    ];

    // Relationships
    public function simpleProducts()
    {
        return $this->hasMany(SimpleProduct::class, 'category_id');
    }

    public function variantProducts()
    {
        return $this->hasMany(VariantProduct::class, 'category_id');
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

    // Accessors
    public function getSimpleProductsCountAttribute()
    {
        return SimpleProduct::where('category_id', $this->_id)->count();
    }

    public function getVariantProductsCountAttribute()
    {
        return VariantProduct::where('category_id', $this->_id)->count();
    }

    public function getTotalProductsAttribute()
    {
        return $this->simple_products_count + $this->variant_products_count;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/default-category.png');
    }
}
