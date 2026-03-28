<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $collection = 'purchase_returns';

    protected $fillable = [
        'return_number',
        'purchase_invoice_id',
        'party_id',
        'party_type', // 'vendor', 'dealer', 'distributor'
        'party_name',
        'warehouse_id',
        'return_date',
        'reason',
        'notes',
        'total_return_qty',
        'subtotal',
        'total_tax',
        'discount_amount',
        'total_return_amount',
        'status',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_return_qty' => 'float',
        'subtotal' => 'float',
        'total_tax' => 'float',
        'discount_amount' => 'float',
        'total_return_amount' => 'float',
    ];

    public function party()
    {
        if ($this->party_type === 'vendor') {
            return $this->belongsTo(\App\Models\Vendor::class, 'party_id');
        }

        return $this->belongsTo(\App\Models\Customer::class, 'party_id');
    }

    // For backward compatibility
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'party_id')->where('party_type', 'vendor');
    }

    public function dealer()
    {
        return $this->belongsTo(Customer::class, 'party_id')->where('party_type', 'dealer');
    }

    public function distributor()
    {
        return $this->belongsTo(Customer::class, 'party_id')->where('party_type', 'distributor');
    }

    // Helper to get party name
    public function getPartyNameAttribute()
    {
        if ($this->party) {
            if ($this->party_type === 'vendor') {
                return $this->party->company_name;
            } else {
                return $this->party->name;
            }
        }
        return 'N/A';
    }

    // Helper to get party phone
    public function getPartyPhoneAttribute()
    {
        if ($this->party) {
            return $this->party->phone ?? '-';
        }
        return '-';
    }

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_return_id');
    }

    public function debitNote()
    {
        return $this->hasOne(DebitNote::class, 'purchase_return_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'badge-secondary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger'
        ];
        return $badges[$this->status] ?? 'badge-secondary';
    }

    private function decimalToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
