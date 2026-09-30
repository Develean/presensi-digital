<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nisn',
        'name',
        'gender',
        'class_id',
        'barcode_token',
        'face_descriptor',
        'device_id',
        'device_name',
        'device_locked_at',
        'parent_name',
        'parent_phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'device_locked_at' => 'datetime',
        ];
    }

    public function isDeviceLocked(): bool
    {
        return !empty($this->device_id);
    }

    public function lockToDevice(string $deviceId, ?string $deviceName = null): void
    {
        $this->update([
            'device_id' => $deviceId,
            'device_name' => $deviceName ?: 'Smartphone Siswa',
            'device_locked_at' => now(),
        ]);
    }

    public function resetDevice(): void
    {
        $this->update([
            'device_id' => null,
            'device_name' => null,
            'device_locked_at' => null,
        ]);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function waLogs(): HasMany
    {
        return $this->hasMany(WaLog::class, 'student_id');
    }
}
