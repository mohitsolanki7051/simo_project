<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class FrontendEnquiry extends Model
{
    use HasFactory;

    protected $collection = 'frontend_enquiries';

    protected $fillable = [
        'type',           // 'dealer', 'contact', 'catalogue'
        'name',
        'phone',
        'email',
        'city_state',
        'business_name',
        'message',
        'status',         // 'new', 'contacted', 'resolved', 'closed'
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Helper scope for filtering by type
    public function scopeOfType($query, $type)
    {
        if ($type && $type !== 'all') {
            return $query->where('type', $type);
        }
        return $query;
    }

    // Helper scope for status
    public function scopeOfStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }
}
