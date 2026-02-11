<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SupplierLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'date',
        'transaction_type', // opening, purchase, payment, adjustment
        'reference_id',
        'reference_no',
        'debit',
        'credit',
        'balance',
        'description',
    ];

    protected $casts = [
        'date' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Accessors
    public function getFormattedDebitAttribute()
    {
        return $this->debit > 0 ? '₹' . number_format($this->debit, 2) : '-';
    }

    public function getFormattedCreditAttribute()
    {
        return $this->credit > 0 ? '₹' . number_format($this->credit, 2) : '-';
    }

    public function getFormattedBalanceAttribute()
    {
        $balance = abs($this->balance);
        $type = $this->balance >= 0 ? 'Dr' : 'Cr';
        return '₹' . number_format($balance, 2) . ' ' . $type;
    }

    public function getTransactionTypeDisplayAttribute()
    {
        $types = [
            'opening' => 'Opening Balance',
            'purchase' => 'Purchase',
            'payment' => 'Payment',
            'adjustment' => 'Adjustment',
        ];

        return $types[$this->transaction_type] ?? 'Unknown';
    }

    // Static Methods
    public static function createOpeningEntry($supplierId, $amount, $balanceType, $date)
    {
        $debit = $balanceType === 'debit' ? $amount : 0;
        $credit = $balanceType === 'credit' ? $amount : 0;

        $entry = static::create([
            'supplier_id' => $supplierId,
            'date' => $date,
            'transaction_type' => 'opening',
            'reference_id' => null,
            'reference_no' => 'OPENING',
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $debit - $credit,
            'description' => 'Opening Balance',
        ]);

        static::recalculateBalance($supplierId);

        return $entry;
    }

    public static function recalculateBalance($supplierId)
    {
        $entries = static::where('supplier_id', $supplierId)
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $runningBalance = 0;

        foreach ($entries as $entry) {
            $runningBalance += $entry->debit - $entry->credit;
            $entry->balance = $runningBalance;
            $entry->save();
        }

        // Update supplier's current balance
        $supplier = Supplier::find($supplierId);
        if ($supplier) {
            $supplier->current_balance = $runningBalance;
            $supplier->save();
        }

        return $runningBalance;
    }

    public static function getSupplierBalance($supplierId, $asOfDate = null)
    {
        $query = static::where('supplier_id', $supplierId);

        if ($asOfDate) {
            $query->where('date', '<=', $asOfDate);
        }

        $lastEntry = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastEntry ? $lastEntry->balance : 0;
    }

    public static function getSupplierLedgerReport($supplierId, $startDate = null, $endDate = null)
    {
        $query = static::where('supplier_id', $supplierId);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
