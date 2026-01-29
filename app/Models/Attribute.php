<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value', // SINGLE VALUE instead of array
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // For specific attribute types
    public function scopeColorTemperature($query)
    {
        return $query->where('type', 'color_temperature');
    }

    public function scopeWatt($query)
    {
        return $query->where('type', 'watt');
    }

    public function scopeShape($query)
    {
        return $query->where('type', 'shape');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('type', 'asc')->orderBy('value', 'asc');
    }

    // Static methods to get all values by type
    public static function getColorTemperatures()
    {
        return self::active()->colorTemperature()->pluck('value')->toArray();
    }

    public static function getWattValues()
    {
        return self::active()->watt()->pluck('value')->toArray();
    }

    public static function getShapeValues()
    {
        return self::active()->shape()->pluck('value')->toArray();
    }

    // Get all grouped attributes
    public static function getGroupedAttributes()
    {
        return [
            'color_temperature' => self::getColorTemperatures(),
            'watt' => self::getWattValues(),
            'shape' => self::getShapeValues()
        ];
    }
}
