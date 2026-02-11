<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $collection = 'warehouse_stocks';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_type',
        'variant_id',
        'quantity',
        'min_stock_alert',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock_alert' => 'integer',
    ];



    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product()
    {
        if ($this->product_type === 'simple') {
            return $this->belongsTo(SimpleProduct::class, 'product_id');
        } else {
            return $this->belongsTo(VariantProduct::class, 'product_id');
        }
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= min_stock_alert');
    }
}
