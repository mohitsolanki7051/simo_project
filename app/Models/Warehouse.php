<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'pincode',
        'phone',
        'email',
        'manager_name',
        'status',
        'is_main',
    ];

    protected $casts = [
        'status' => 'string',
        'is_main' => 'boolean',
    ];

    // ✅ Scope for main warehouse
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // ✅ Check if this is the main warehouse
    public function isMain()
    {
        return $this->is_main === true;
    }

    // ✅ Check if warehouse is active
    public function isActive()
    {
        return $this->status === 'active';
    }

    // ✅ Get status badge class
    public function getStatusBadgeClass()
    {
        return $this->status === 'active' ? 'badge-success' : 'badge-danger';
    }

    // ✅ Get status text
    public function getStatusText()
    {
        return $this->status === 'active' ? 'Active' : 'Inactive';
    }

    // ✅ Relationships
    public function variantProducts()
    {
        return $this->hasMany(VariantProduct::class);
    }

    public function simpleProducts()
    {
        return $this->hasMany(SimpleProduct::class);
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function warehouseMovements()
    {
        return $this->hasMany(WarehouseMovement::class);
    }
}
