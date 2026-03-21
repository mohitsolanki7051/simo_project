<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $collection = 'purchase_invoices';

    protected $fillable = [
        'invoice_number',
        'public_token',
        'invoice_type',
        'invoice_date',
        'vendor_id',
        'purchase_executive_id',
        'warehouse_id',
        'billing_address',
        'shipping_address',
        'payment_terms',
        'due_date',
        'po_number',
        'notes',
        'total_mrp',
        'subtotal',
        'discount_total',
        'tax_total',
        'cgst_total',
        'sgst_total',
        'igst_total',
        'tax_type',
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
        'subtotal' => 'float',
        'discount_total' => 'float',
        'tax_total' => 'float',
        'cgst_total' => 'float',
        'sgst_total' => 'float',
        'igst_total' => 'float',
        'grand_total' => 'float',
        'round_off' => 'float',
        'total_paid' => 'float',
        'balance_amount' => 'float',
        'extra_discount' => 'float',
        'extra_charge' => 'float',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function purchaseExecutive()
    {
        return $this->belongsTo(Salesman::class, 'purchase_executive_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'purchase_invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
