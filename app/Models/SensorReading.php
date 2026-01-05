<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'petak1_moisture',
        'petak2_moisture',
        'petak3_moisture',
        'petak4_moisture',
        'water_main',
        'water_mid',
        'water_tank',
        'pump_status',
        'gate_main',
        'gate1',
        'gate2',
        'gate3',
        'gate4',
        'system_status',
    ];

    protected $casts = [
        'pump_status' => 'boolean',
        'water_main' => 'float',
        'water_mid' => 'float',
        'water_tank' => 'float',
    ];

    /**
     * Get moisture status label
     */
    public static function getMoistureStatus(int $value): string
    {
        if ($value <= 30)
            return 'Kering';
        if ($value <= 60)
            return 'Lembab';
        return 'Basah';
    }

    /**
     * Get moisture status color
     */
    public static function getMoistureColor(int $value): string
    {
        if ($value <= 30)
            return 'danger';
        if ($value <= 60)
            return 'warning';
        return 'success';
    }
}
