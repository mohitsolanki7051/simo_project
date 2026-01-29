<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        // Order Information
        'order_number',
        'order_date',
        'order_status',
        'payment_status',
        'payment_method',

        // Customer Information
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_age',

        // Billing Address
        'billing_address_line1',
        'billing_address_line2',
        'billing_city',
        'billing_state',
        'billing_pincode',
        'billing_country',

        // Shipping Address
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_pincode',
        'shipping_country',
        'same_as_billing',

        // Order Items (Array of product details)
        'items',

        // Pricing
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_charges',
        'total_amount',

        // Additional Information
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'items' => 'array',
        'order_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'same_as_billing' => 'boolean',
    ];

    // Generate unique order number
    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::where('order_number', 'like', $prefix . $date . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    // Accessors
    public function getFormattedOrderDateAttribute()
    {
        return $this->order_date ? $this->order_date->format('d M Y, h:i A') : '';
    }

    public function getFormattedSubtotalAttribute()
    {
        return '₹' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return '₹' . number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return '₹' . number_format($this->discount_amount, 2);
    }

    public function getFormattedShippingChargesAttribute()
    {
        return '₹' . number_format($this->shipping_charges, 2);
    }

    public function getFormattedTotalAmountAttribute()
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getOrderStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'processing' => '<span class="badge badge-info">Processing</span>',
            'completed' => '<span class="badge badge-success">Completed</span>',
            'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
            'on_hold' => '<span class="badge badge-secondary">On Hold</span>',
        ];

        return $badges[$this->order_status] ?? '<span class="badge badge-secondary">' . ucfirst($this->order_status) . '</span>';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'paid' => '<span class="badge badge-success">Paid</span>',
            'unpaid' => '<span class="badge badge-danger">Unpaid</span>',
            'partially_paid' => '<span class="badge badge-warning">Partially Paid</span>',
            'refunded' => '<span class="badge badge-info">Refunded</span>',
        ];

        return $badges[$this->payment_status] ?? '<span class="badge badge-secondary">' . ucfirst($this->payment_status) . '</span>';
    }

    public function getTotalItemsAttribute()
    {
        if (!$this->items || !is_array($this->items)) {
            return 0;
        }

        return array_sum(array_column($this->items, 'quantity'));
    }

    public function getFullBillingAddressAttribute()
    {
        $parts = array_filter([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $this->billing_city,
            $this->billing_state,
            $this->billing_pincode,
            $this->billing_country,
        ]);

        return implode(', ', $parts);
    }

    public function getFullShippingAddressAttribute()
    {
        if ($this->same_as_billing) {
            return $this->full_billing_address;
        }

        $parts = array_filter([
            $this->shipping_address_line1,
            $this->shipping_address_line2,
            $this->shipping_city,
            $this->shipping_state,
            $this->shipping_pincode,
            $this->shipping_country,
        ]);

        return implode(', ', $parts);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('order_status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('order_status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('order_status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    // Methods
    public function calculateTotals()
    {
        $subtotal = 0;
        $taxAmount = 0;

        if ($this->items && is_array($this->items)) {
            foreach ($this->items as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $itemTax = ($itemSubtotal * $item['gst']) / 100;

                $subtotal += $itemSubtotal;
                $taxAmount += $itemTax;
            }
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $subtotal + $taxAmount + ($this->shipping_charges ?? 0) - ($this->discount_amount ?? 0);
    }
}
