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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('attendance_date')->index();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->enum('status_in', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('alpa');
            $table->enum('status_out', ['pulang_normal', 'pulang_awal', 'belum_pulang'])->default('belum_pulang');
            $table->enum('method_in', ['face', 'barcode', 'manual'])->nullable();
            $table->enum('method_out', ['face', 'barcode', 'manual'])->nullable();
            $table->string('photo_in_path')->nullable(); // Snapshot web cam bukti scan
            $table->string('photo_out_path')->nullable();
            $table->text('notes')->nullable(); // Keterangan dari guru/surat izin
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); // Audit trail guru/admin yang mengubah
            $table->timestamps();

            // Cegah duplikasi presensi siswa pada tanggal yang sama
            $table->unique(['student_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
