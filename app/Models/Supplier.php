<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_code',
        'name',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'gstin',
        'pan',
        'contact_person',
        'bank_name',
        'account_number',
        'ifsc_code',
        'payment_terms',
        'opening_balance',
        'balance_type', // debit or credit
        'current_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    // Relationships
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(SupplierLedger::class)->orderBy('date', 'desc');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getFormattedCurrentBalanceAttribute()
    {
        $balance = $this->current_balance ?? 0;
        $type = $balance >= 0 ? 'Dr' : 'Cr';
        return '₹' . number_format(abs($balance), 2) . ' ' . $type;
    }

    // Methods
    public static function generateSupplierCode()
    {
        $lastSupplier = static::orderBy('supplier_code', 'desc')->first();

        if ($lastSupplier && $lastSupplier->supplier_code) {
            $lastNumber = (int) substr($lastSupplier->supplier_code, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'SUP' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function updateBalance($amount, $type = 'debit')
    {
        // Debit = Supplier ko dena hai (increase liability)
        // Credit = Supplier ne advance diya hai (decrease liability)

        if ($type === 'debit') {
            $this->current_balance += $amount;
        } else {
            $this->current_balance -= $amount;
        }

        $this->save();
    }

    public function getTotalPurchases()
    {
        return $this->purchases()->sum('grand_total');
    }

    public function getTotalPayments()
    {
        return $this->payments()->sum('amount');
    }

    public function getDueAmount()
    {
        return $this->current_balance;
    }
}
