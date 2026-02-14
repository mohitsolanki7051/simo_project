<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Otp extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'otps';

    protected $fillable = [
        'email',
        'otp',
        'expires_at'
    ];
}
