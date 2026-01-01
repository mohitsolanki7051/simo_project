<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class UserMongo extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'age'
    ];
}
