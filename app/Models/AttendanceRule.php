<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'time_in_start',
        'time_in_deadline',
        'time_in_late_limit',
        'time_out_start',
        'time_out_end',
        'active_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'active_days' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
