<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $collection = 'warranty_claims';

    protected $fillable = [
        'warranty_claim_number',   // WC/25-26/0001
        'sales_invoice_id',
        'sales_invoice_item_id',
        'party_id',
        'product_id',
        'variant_id',
        'product_type',             // simple or variant
        'claim_date',
        'warranty_status',          // valid / expired
        'claim_type',                // replacement
        'replacement_status',        // pending / done
        'repair_status',             // pending / completed
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'repaired_by',
        'repaired_at',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'approved_at' => 'datetime',
        'repaired_at' => 'datetime',
    ];

    // Relationships
    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function salesInvoiceItem()
    {
        return $this->belongsTo(SalesInvoiceItem::class, 'sales_invoice_item_id');
    }

    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function simpleProduct()
    {
        return $this->belongsTo(SimpleProduct::class, 'product_id');
    }

    public function variantProduct()
    {
        return $this->belongsTo(VariantProduct::class, 'product_id');
    }

    // public function variant()
    // {
    //     return $this->belongsTo(VariantProduct::class, 'variant_id');
    // }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function repairer()
    {
        return $this->belongsTo(Admin::class, 'repaired_by');
    }

    // Accessors
    public function getWarrantyStatusBadgeAttribute()
    {
        $badges = [
            'valid' => 'badge-success',
            'expired' => 'badge-danger'
        ];

        return $badges[$this->warranty_status] ?? 'badge-secondary';
    }

    public function getReplacementStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'done' => 'badge-success'
        ];

        return $badges[$this->replacement_status] ?? 'badge-secondary';
    }

    public function getRepairStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'completed' => 'badge-success'
        ];

        return $badges[$this->repair_status] ?? 'badge-secondary';
    }

    // Helper Methods
    public function isReplacementDone()
    {
        return $this->replacement_status === 'done';
    }

    public function isRepairCompleted()
    {
        return $this->repair_status === 'completed';
    }

    public function canApprove()
    {
        return $this->warranty_status === 'valid' &&
               $this->replacement_status === 'pending' &&
               $this->repair_status === 'pending';
    }

    public function canMarkRepairCompleted()
    {
        return $this->replacement_status === 'done' &&
               $this->repair_status === 'pending';
    }
}
