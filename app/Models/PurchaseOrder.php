<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic Information
        'po_number',
        'po_date',
        'supplier_id',
        'warehouse_id',

        // Supplier Details
        'supplier_name',
        'supplier_email',
        'supplier_phone',
        'supplier_address',

        // Purchase Details
        'reference_number',
        'payment_terms',
        'expected_delivery_date',
        'shipping_method',
        '_address',

        // Items
        'items', // Array of purchase items with variants

        // Financial
        'subtotal',
        'tax_amount',
        'discount_amount',
        'discount_type', // percentage or fixed
        'shipping_charges',
        'other_charges',
        'total_amount',

        // Status
        'status', // draft, pending, approved, received, cancelled
        'payment_status', // unpaid, partial, paid
        'received_status', // not_received, partial, fully_received

        // Notes
        'terms_conditions',
        'notes',

        // Tracking
        'created_by',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'items' => 'array',
        'po_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_charges' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
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

    // Helper method to convert MongoDB Decimal128 to float
    private function convertToFloat($value)
    {
        if ($value instanceof \MongoDB\BSON\Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }

    // Accessors
    public function getTotalItemsAttribute()
    {
        if (!$this->items || !is_array($this->items)) {
            return 0;
        }
        return count($this->items);
    }

    public function getTotalQuantityAttribute()
    {
        if (!$this->items || !is_array($this->items)) {
            return 0;
        }

        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['quantity'] ?? 0;
        }
        return $total;
    }

    public function getFormattedSubtotalAttribute()
    {
        return '₹' . number_format($this->convertToFloat($this->subtotal), 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return '₹' . number_format($this->convertToFloat($this->tax_amount), 2);
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return '₹' . number_format($this->convertToFloat($this->discount_amount), 2);
    }

    public function getFormattedTotalAmountAttribute()
    {
        return '₹' . number_format($this->convertToFloat($this->total_amount), 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge badge-secondary">Draft</span>',
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'approved' => '<span class="badge badge-info">Approved</span>',
            'received' => '<span class="badge badge-success">Received</span>',
            'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'unpaid' => '<span class="badge badge-danger">Unpaid</span>',
            'partial' => '<span class="badge badge-warning">Partial</span>',
            'paid' => '<span class="badge badge-success">Paid</span>',
        ];

        return $badges[$this->payment_status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        if ($warehouseId) {
            return $query->where('warehouse_id', $warehouseId);
        }
        return $query;
    }

    public function scopeBySupplier($query, $supplierId)
    {
        if ($supplierId) {
            return $query->where('supplier_id', $supplierId);
        }
        return $query;
    }

    // Methods
    public function generatePONumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "PO-{$year}{$month}-";

        $lastPO = static::where('po_number', 'like', "{$prefix}%")
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPO) {
            $lastNumber = (int) substr($lastPO->po_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $subtotal = 0;
        $taxAmount = 0;

        if ($this->items && is_array($this->items)) {
            foreach ($this->items as $item) {
                $quantity = $item['quantity'] ?? 0;
                $unitPrice = $this->convertToFloat($item['unit_price'] ?? 0);
                $gst = $this->convertToFloat($item['gst'] ?? 0);

                $itemTotal = $quantity * $unitPrice;
                $itemTax = ($itemTotal * $gst) / 100;

                $subtotal += $itemTotal;
                $taxAmount += $itemTax;
            }
        }

        $discountAmount = 0;
        if ($this->discount_type === 'percentage') {
            $discountAmount = ($subtotal * $this->convertToFloat($this->discount_amount ?? 0)) / 100;
        } else {
            $discountAmount = $this->convertToFloat($this->discount_amount ?? 0);
        }

        $shippingCharges = $this->convertToFloat($this->shipping_charges ?? 0);
        $otherCharges = $this->convertToFloat($this->other_charges ?? 0);

        $totalAmount = $subtotal + $taxAmount - $discountAmount + $shippingCharges + $otherCharges;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }

    public function approve($userId)
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function markAsReceived($userId)
    {
        $this->status = 'received';
        $this->received_status = 'fully_received';
        $this->received_by = $userId;
        $this->received_at = now();
        $this->save();
    }

    public function cancel($userId, $reason = null)
    {
        $this->status = 'cancelled';
        $this->cancelled_by = $userId;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->save();
    }

    public function canEdit()
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function canApprove()
    {
        return $this->status === 'pending';
    }

    public function canReceive()
    {
        return $this->status === 'approved';
    }

    public function canCancel()
    {
        return !in_array($this->status, ['received', 'cancelled']);
    }
}
