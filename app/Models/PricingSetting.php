<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PricingSetting extends Model
{
    protected $collection = 'pricing_settings';

    protected $fillable = [
        'dealer_percentage',
        'distributor_percentage',
    ];

    protected $casts = [
        'dealer_percentage' => 'decimal:2',
        'distributor_percentage' => 'decimal:2',
    ];
}
