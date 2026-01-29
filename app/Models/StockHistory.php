<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'adjustment_type',
        'old_quantity',
        'new_quantity',
        'adjustment_quantity',
        'date',
        'remarks',
        'adjusted_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'adjusted_by');
    }
}
