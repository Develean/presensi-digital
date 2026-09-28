<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Senin - Kamis", "Jumat"
            $table->time('time_in_start'); // e.g. "06:00:00"
            $table->time('time_in_deadline'); // e.g. "07:15:00" (Batas Tepat Waktu)
            $table->time('time_in_late_limit'); // e.g. "07:30:00" (Batas Toleransi Terlambat & Trigger WA)
            $table->time('time_out_start'); // e.g. "15:00:00"
            $table->time('time_out_end'); // e.g. "17:00:00"
            $table->json('active_days'); // e.g. [1, 2, 3, 4, 5] (1=Monday, 7=Sunday)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_rules');
    }
};
