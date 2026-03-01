<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\Decimal128;

class PaymentIn extends Model
{
    use HasFactory;

    protected $collection = 'payment_ins';

    protected $fillable = [
        'payment_number',
        'payment_date',
        'party_id',
        'party_type',
        'total_amount',
        'payment_method',
        'reference_no',
        'notes',
        'status', // 'completed', 'cancelled'
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_amount' => 'float',
    ];

    // Relationships
    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function items()
    {
        return $this->hasMany(PaymentInItem::class, 'payment_in_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Generate Payment Number with format: SIM/PI/25-26/000001
    public static function generatePaymentNumber()
    {
        // Get current financial year
        $financialYear = self::getFinancialYear();

        // Get the last payment for this financial year
        $lastPayment = self::where('payment_number', 'regex', "/^SIM\/PI\/{$financialYear}\/\d+$/")
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            // Extract the last number from payment number
            preg_match('/(\d+)$/', $lastPayment->payment_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "SIM/PI/{$financialYear}/{$newNumber}";
    }

    // Get current financial year (e.g., 25-26)
    private static function getFinancialYear()
    {
        $currentMonth = (int)date('m');
        $currentYear = (int)date('y');

        // Financial year in India starts from April
        if ($currentMonth >= 4) {
            // Apr to Dec: 25-26
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            // Jan to Mar: 24-25
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    // Accessor for total amount as float
    public function getTotalAmountAttribute($value)
    {
        if ($value instanceof Decimal128) {
            return (float) $value->__toString();
        }
        return (float) $value;
    }
}
