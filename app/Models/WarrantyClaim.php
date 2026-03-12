<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $collection = 'warranty_claims';

    protected $fillable = [
        'warranty_claim_number',
        'sales_invoice_id',
        'sales_invoice_item_id',
        'party_id',
        'product_id',
        'variant_id',
        'product_type',
        'claim_date',
        'warranty_status',
        'claim_type',
        'replacement_status',
        'repair_status',
        'claimed_qty',
        'replaced_qty',
        'repaired_qty',
        'scrapped_qty',           // ← NEW: kitne scrap huye
        'warehouse_id',
        'defective_stock_status', // pending_repair | repaired | scrapped
        'notes',
        'scrap_reason',           // ← NEW: scrap ka reason
        'created_by',
        'approved_by',
        'approved_at',
        'repaired_by',
        'repaired_at',
        'scrapped_by',            // ← NEW
        'scrapped_at',            // ← NEW
    ];

    protected $casts = [
        'claim_date'   => 'date',
        'approved_at'  => 'datetime',
        'repaired_at'  => 'datetime',
        'scrapped_at'  => 'datetime',   // ← NEW
        'claimed_qty'  => 'integer',
        'replaced_qty' => 'integer',
        'repaired_qty' => 'integer',
        'scrapped_qty' => 'integer',    // ← NEW
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

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

    public function scrapper()
    {
        return $this->belongsTo(Admin::class, 'scrapped_by'); // ← NEW
    }

    // ─── Accessors / Badges ──────────────────────────────────────

    public function getWarrantyStatusBadgeAttribute()
    {
        return ['valid' => 'badge-success', 'expired' => 'badge-danger'][$this->warranty_status] ?? 'badge-secondary';
    }

    public function getReplacementStatusBadgeAttribute()
    {
        return ['pending' => 'badge-warning', 'done' => 'badge-success'][$this->replacement_status] ?? 'badge-secondary';
    }

    public function getRepairStatusBadgeAttribute()
    {
        return ['pending' => 'badge-warning', 'completed' => 'badge-success'][$this->repair_status] ?? 'badge-secondary';
    }

    // ─── Helper Methods ──────────────────────────────────────────

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
            in_array($this->replacement_status, ['pending', 'partial']) &&
            $this->replacementRemainingQty() > 0;
    }

    public function replacementRemainingQty()
    {
        return max(0, (int) $this->claimed_qty - (int) $this->replaced_qty);
    }

    /**
     * Repair button dikhao jab koi replaced unit abhi repair/scrap ke liye pending ho
     */
    public function canMarkRepairCompleted()
    {
        return $this->replaced_qty > 0 && $this->repairRemainingQty() > 0;
    }

    /**
     * Scrap button dikhao same condition pe jab repair pending ho
     */
    public function canMarkScrapped()
    {
        return $this->replaced_qty > 0 && $this->repairRemainingQty() > 0;
    }

    /**
     * Pending units = replaced - (repaired + scrapped)
     * Ye same pool hai — repair ya scrap dono isi se aate hain
     */
    public function repairRemainingQty()
    {
        return max(0, (int) $this->replaced_qty - (int) $this->repaired_qty - (int) $this->scrapped_qty);
    }

    public static function remainingQty($salesInvoiceItemId, $purchasedQty)
    {
        $totalClaimed = self::where('sales_invoice_item_id', $salesInvoiceItemId)
            ->whereIn('replacement_status', ['pending', 'done'])
            ->sum('claimed_qty');

        return max(0, $purchasedQty - $totalClaimed);
    }
    public function isEditable(): bool
    {
        return $this->approved_at === null;
    }
}
