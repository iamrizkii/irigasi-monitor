<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'type',
        'message',
        'petak',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Scope for unread alerts
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Get alert icon based on type
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'drought' => 'fa-sun',
            'pump_error' => 'fa-exclamation-triangle',
            'water_low' => 'fa-tint-slash',
            default => 'fa-info-circle',
        };
    }

    /**
     * Get alert color based on type
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            'drought' => 'danger',
            'pump_error' => 'warning',
            'water_low' => 'info',
            default => 'secondary',
        };
    }
}
