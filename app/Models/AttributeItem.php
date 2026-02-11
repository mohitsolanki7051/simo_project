<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class AttributeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_type_id',
        'value',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];
      public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_type_id');
    }

    /**
     * Scope a query to only include active items.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
