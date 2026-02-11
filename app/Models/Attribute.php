<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Relationship with AttributeItem
    public function items()
    {
        return $this->hasMany(AttributeItem::class, 'attribute_type_id');
    }
      public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Scope a query to only include active attributes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

}
