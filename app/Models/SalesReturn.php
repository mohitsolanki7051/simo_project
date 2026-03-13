<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

    protected $collection = 'sales_returns';

    protected $fillable = [
        'return_number',
        'sales_invoice_id',
        'party_id',
        'warehouse_id',
        'return_date',
        'reason',
        'notes',
        'total_return_qty',
        'subtotal',              // ✅ New field
        'total_tax',              // ✅ New field
        'discount_amount',        // ✅ New field
        'total_return_amount',
        'status',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_return_qty' => 'decimal:2',
        'subtotal' => 'decimal:2',              // ✅ New cast
        'total_tax' => 'decimal:2',              // ✅ New cast
        'discount_amount' => 'decimal:2',        // ✅ New cast
        'total_return_amount' => 'decimal:2',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class, 'sales_return_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'badge-secondary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger'
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    // Helper to get discount percentage from original invoice
    public function getDiscountPercentageAttribute()
    {
        if ($this->subtotal > 0 && $this->discount_amount > 0) {
            return round(($this->discount_amount / $this->subtotal) * 100, 2);
        }
        return 0;
    }
}
