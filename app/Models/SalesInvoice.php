<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SalesInvoice extends Model
{
    use HasFactory;

    protected $collection = 'sales_invoices';

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'customer_id',
        'warehouse_id',
        // addresses
        'billing_address',
        'shipping_address',
        // invoice meta
        'payment_terms',
        'due_date',
        'po_number',
        'vehicle_no',
        'colours',
        'notes',
        // totals
        'total_mrp',
        'subtotal',
        'discount_total',
        'tax_total',
        'extra_discount',
        'extra_discount_type',
        'extra_charge',
        'charge_name',
        'round_off',
        'grand_total',
        'total_paid',
        'balance_amount',
        'payment_status',
        'status',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'round_off' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'extra_discount' => 'decimal:2',
        'extra_charge' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class, 'sales_invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(SalesPayment::class, 'sales_invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Accessors
    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'paid' => 'badge-success',
            'unpaid' => 'badge-danger',
            'partial' => 'badge-warning'
        ];

        return $badges[$this->payment_status] ?? 'badge-secondary';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'badge-secondary',
            'confirmed' => 'badge-primary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger'
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getDueInDaysAttribute()
    {
        if (!$this->due_date) {
            return '-';
        }

        $dueDate = \Carbon\Carbon::parse($this->due_date);
        $today = \Carbon\Carbon::today();

        if ($dueDate->isPast()) {
            $diff = $today->diffInDays($dueDate);
            return $diff > 0 ? "{$diff} Days Overdue" : "Today";
        } else {
            $diff = $today->diffInDays($dueDate);
            return "{$diff} Days";
        }
    }
}
