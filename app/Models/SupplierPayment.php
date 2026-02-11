<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_no',
        'purchase_id',
        'supplier_id',
        'amount',
        'payment_mode', // cash, upi, bank, cheque
        'payment_date',
        'reference_no',
        'cheque_no',
        'cheque_date',
        'bank_name',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'cheque_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ledgerEntry()
    {
        return $this->hasOne(SupplierLedger::class, 'reference_id')->where('transaction_type', 'payment');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getPaymentModeDisplayAttribute()
    {
        $modes = [
            'cash' => 'Cash',
            'upi' => 'UPI',
            'bank' => 'Bank Transfer',
            'cheque' => 'Cheque',
        ];

        return $modes[$this->payment_mode] ?? 'Unknown';
    }

    // Methods
    public static function generatePaymentNo()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "PAY-{$year}{$month}-";

        $lastPayment = static::where('payment_no', 'like', "{$prefix}%")
            ->orderBy('payment_no', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function createLedgerEntry()
    {
        SupplierLedger::create([
            'supplier_id' => $this->supplier_id,
            'date' => $this->payment_date,
            'transaction_type' => 'payment',
            'reference_id' => $this->id,
            'reference_no' => $this->payment_no,
            'debit' => 0,
            'credit' => $this->amount,
            'balance' => 0, // Will be calculated
            'description' => "Payment - {$this->payment_no}" . ($this->purchase ? " for {$this->purchase->purchase_no}" : ''),
        ]);

        // Update supplier balance
        $supplier = $this->supplier;
        $supplier->updateBalance($this->amount, 'credit');

        // Recalculate all ledger balances
        SupplierLedger::recalculateBalance($this->supplier_id);
    }

    public function updatePurchasePayment()
    {
        if ($this->purchase) {
            $this->purchase->addPayment($this->amount);
        }
    }
}
