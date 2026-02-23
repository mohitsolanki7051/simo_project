<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

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
        'commission_enabled',
        'customer_commission_percent',  // Changed from retailer_commission_percent
        'dealer_commission_percent',
        'distributor_commission_percent',
        'status',
        'notes'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'commission_enabled' => 'boolean',
        'fixed_salary' => 'float',
        'customer_commission_percent' => 'float',  // Changed
        'dealer_commission_percent' => 'float',
        'distributor_commission_percent' => 'float',
    ];

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
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    public function getFormattedJoiningDateAttribute()
    {
        return $this->joining_date ? date('d M Y', strtotime($this->joining_date)) : 'N/A';
    }

    public function getFormattedFixedSalaryAttribute()
    {
        return $this->fixed_salary ? '₹' . number_format($this->fixed_salary, 2) : '₹0.00';
    }

    public function getSalaryTypeTextAttribute()
    {
        $types = [
            'fixed' => 'Fixed Only',
            'commission' => 'Commission Only',
            'both' => 'Fixed + Commission'
        ];
        return $types[$this->salary_type] ?? ucfirst($this->salary_type);
    }

    public function getCommissionStatusAttribute()
    {
        return $this->commission_enabled ? 'Enabled' : 'Disabled';
    }
}
