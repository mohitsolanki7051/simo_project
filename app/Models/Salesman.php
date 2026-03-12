<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Salesman extends Model
{
    use HasFactory;

    protected $collection = 'salesmen';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'joining_date',
        'salary_type',
        'fixed_salary',
        'fixed_salary_period', // New field
        'commission_enabled',
        'commission_period', // New field
        'customer_commission_percent',
        'dealer_commission_percent',
        'distributor_commission_percent',
        'status',
        'notes',
    ];

    protected $casts = [
        'joining_date'                  => 'date',
        'commission_enabled'            => 'boolean',
        'fixed_salary'                  => 'float',
        'customer_commission_percent'   => 'float',
        'dealer_commission_percent'     => 'float',
        'distributor_commission_percent'=> 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class, 'salesman_id');
    }

    public function parties()
    {
        return $this->hasMany(Customer::class, 'salesman_id');
    }

    public function payments()
    {
        return $this->hasMany(SalesmanPayment::class, 'salesman_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function getCommissionRateForPartyType($partyType)
    {
        return match ($partyType) {
            'customer'    => $this->customer_commission_percent    ?? 0,
            'dealer'      => $this->dealer_commission_percent      ?? 0,
            'distributor' => $this->distributor_commission_percent ?? 0,
            default       => 0,
        };
    }

    /**
     * Get the start date of a period given type and anchor date.
     * period_type: 1month | 6months | 1year
     */
    public function getPeriodStart(string $periodType, ?Carbon $anchor = null): Carbon
    {
        $anchor = $anchor ?? Carbon::now();

        return match ($periodType) {
            '6months' => $anchor->copy()->subMonths(6)->startOfDay(),
            '1year'   => $anchor->copy()->subYear()->startOfDay(),
            default   => $anchor->copy()->startOfMonth(), // 1month
        };
    }

    /**
     * Last commission payment of a given period type.
     */
    public function lastCommissionPayment(?string $periodType = null)
    {
        $q = $this->payments()->where('payment_type', 'commission');
        if ($periodType) {
            $q->where('period_type', $periodType);
        }
        return $q->orderBy('paid_at', 'desc')->first();
    }

    /**
     * Accessor: formatted fixed salary
     */
    public function getFormattedFixedSalaryAttribute(): string
    {
        return '₹' . number_format($this->fixed_salary ?? 0, 2);
    }

    /**
     * Accessor: salary type text
     */
    public function getSalaryTypeTextAttribute(): string
    {
        return match ($this->salary_type) {
            'fixed'      => 'Fixed',
            'commission' => 'Commission',
            'both'       => 'Fixed + Commission',
            default      => ucfirst($this->salary_type ?? ''),
        };
    }

    /**
     * Accessor: formatted joining date
     */
    public function getFormattedJoiningDateAttribute(): string
    {
        return $this->joining_date?->format('d M Y') ?? 'N/A';
    }
}
