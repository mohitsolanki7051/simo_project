<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'parent_id',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'parent_id' => 'string'
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithChildren($query)
    {
        return $query->with(['children' => function($q) {
            $q->orderBy('sort_order', 'asc');
        }]);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    // Accessors
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    public function getChildrenCountAttribute()
    {
        return $this->children()->count();
    }

    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    // Methods
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function hasProducts()
    {
        return $this->products()->count() > 0;
    }

    public function canBeDeleted()
    {
        return !$this->hasChildren() && !$this->hasProducts();
    }
}
