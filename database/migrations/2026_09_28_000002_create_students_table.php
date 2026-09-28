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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P']);
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('barcode_token')->unique(); // Unique identifier for Barcode/QR scan
            $table->longText('face_descriptor')->nullable(); // 128-float array from face-api.js as JSON
            $table->string('parent_name');
            $table->string('parent_phone'); // Format: 628xxx for WhatsApp Gateway
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
