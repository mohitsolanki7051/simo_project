<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $collection = 'warehouse_transfers';

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'notes',
        'items',
        'created_by',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'items' => 'array',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', self::STATUS_IN_TRANSIT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Helper methods
    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_IN_TRANSIT => 'badge-info',
            self::STATUS_PARTIALLY_RECEIVED => 'badge-primary',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    public function getStatusText()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function canApprove()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canReceive()
    {
        return in_array($this->status, [self::STATUS_IN_TRANSIT, self::STATUS_PARTIALLY_RECEIVED]);
    }

    public function canCancel()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_TRANSIT]);
    }

    // Calculate total items
    public function getTotalItemsAttribute()
    {
        return collect($this->items)->sum('transfer_quantity');
    }

    // Calculate total received items
    public function getReceivedItemsAttribute()
    {
        return collect($this->items)->sum('received_quantity');
    }

    // Check if transfer is fully received
    public function getIsFullyReceivedAttribute()
    {
        foreach ($this->items as $item) {
            if ($item['transfer_quantity'] > $item['received_quantity']) {
                return false;
            }
        }
        return true;
    }
}
