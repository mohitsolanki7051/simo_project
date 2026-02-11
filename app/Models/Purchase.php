<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_no',
        'supplier_id',
        'warehouse_id',
        'invoice_no',
        'invoice_date',
        'purchase_date',
        'subtotal',
        'discount',
        'discount_type', // percentage or fixed
        'tax_amount',
        'shipping_charge',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status', // paid, partial, unpaid
        'payment_terms',
        'shipping_address',
        'remarks',
        'notes',
        'status', // draft, confirmed, received, cancelled
        'created_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'purchase_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
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

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(SupplierLedger::class, 'reference_id')->where('transaction_type', 'purchase');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    // Accessors
    public function getFormattedGrandTotalAttribute()
    {
        return '₹' . number_format($this->grand_total, 2);
    }

    public function getFormattedDueAmountAttribute()
    {
        return '₹' . number_format($this->due_amount, 2);
    }

    public function getFormattedPaidAmountAttribute()
    {
        return '₹' . number_format($this->paid_amount, 2);
    }

    // Methods
    public static function generatePurchaseNo()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "PUR-{$year}{$month}-";

        $lastPurchase = static::where('purchase_no', 'like', "{$prefix}%")
            ->orderBy('purchase_no', 'desc')
            ->first();

        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->purchase_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $subtotal = $this->items()->sum('total');
        $taxAmount = $this->items()->sum('tax_amount');

        $discountAmount = 0;
        if ($this->discount_type === 'percentage') {
            $discountAmount = ($subtotal * $this->discount) / 100;
        } else {
            $discountAmount = $this->discount;
        }

        $grandTotal = $subtotal + $taxAmount - $discountAmount + $this->shipping_charge;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->grand_total = $grandTotal;
        $this->due_amount = $grandTotal - $this->paid_amount;

        $this->save();
    }

    public function updatePaymentStatus()
    {
        if ($this->paid_amount >= $this->grand_total) {
            $this->payment_status = 'paid';
            $this->due_amount = 0;
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
            $this->due_amount = $this->grand_total - $this->paid_amount;
        } else {
            $this->payment_status = 'unpaid';
            $this->due_amount = $this->grand_total;
        }

        $this->save();
    }

    public function addPayment($amount)
    {
        $this->paid_amount += $amount;
        $this->updatePaymentStatus();
    }

    public function createLedgerEntry()
    {
        SupplierLedger::create([
            'supplier_id' => $this->supplier_id,
            'date' => $this->purchase_date,
            'transaction_type' => 'purchase',
            'reference_id' => $this->id,
            'reference_no' => $this->purchase_no,
            'debit' => $this->grand_total,
            'credit' => 0,
            'balance' => 0, // Will be calculated
            'description' => "Purchase - {$this->purchase_no}",
        ]);

        // Recalculate supplier balance
        $supplier = $this->supplier;
        $supplier->updateBalance($this->grand_total, 'debit');

        // Recalculate all ledger balances
        SupplierLedger::recalculateBalance($this->supplier_id);
    }

    public function canEdit()
    {
        return in_array($this->status, ['draft']);
    }

    public function canDelete()
    {
        return $this->status === 'draft' && $this->payments()->count() === 0;
    }
}
