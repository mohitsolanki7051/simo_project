<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class WarehouseMovement extends Model
{
    use HasFactory;

    protected $collection = 'warehouse_movements';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_type',
        'variant_id',
        'type',
        'quantity',
        'reference_id',
        'remarks', // ✅ Add remarks field
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    const TYPE_OPENING = 'opening';
    const TYPE_SALE = 'sale';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';
    const TYPE_ADJUSTMENT = 'adjustment';

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
}
