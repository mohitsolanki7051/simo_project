<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $collection = 'expenses';

    protected $fillable = [
        'category_name',
        'amount',
        'expense_date',
        'payment_method',
        'reference_no',
        'description'
    ];

    protected $casts = [
        'amount' => 'float',
        'expense_date' => 'datetime'
    ];
}
