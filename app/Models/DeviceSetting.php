<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSetting extends Model
{
    protected $fillable = [
        'mode',
        'pump_command',
        'gate_main_command',
        'gate1_command',
        'gate2_command',
        'gate3_command',
        'gate4_command',
    ];

    protected $casts = [
        'pump_command' => 'boolean',
    ];

    /**
     * Get or create the singleton settings record
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'mode' => 'auto',
            'pump_command' => false,
            'gate_main_command' => 0,
            'gate1_command' => 0,
            'gate2_command' => 0,
            'gate3_command' => 0,
            'gate4_command' => 0,
        ]);
    }
}
