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
        Schema::create('attendance_anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade')->comment('FK ke attendances');
            $table->foreignId('student_id')->constrained('students')->comment('FK ke students');
            $table->date('date')->comment('Tanggal anomali');
            $table->enum('anomaly_type', [
                'duplicate_checkin', 'duplicate_checkout',
                'missing_checkout', 'missing_checkin',
                'unusual_time', 'unusual_location',
                'gps_jump', 'rapid_sequence',
                'device_mismatch', 'other'
            ])->comment('Tipe anomali');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->comment('Tingkat severity');
            $table->text('description')->comment('Deskripsi anomali yang terdeteksi');
            $table->json('data')->nullable()->comment('Data pendukung (JSON: GPS, waktu, device, dll)');

            // Auto-detection info
            $table->string('detection_method', 50)->nullable()->comment('Metode deteksi: cron_job, real_time, manual');
            $table->timestamp('detected_at')->comment('Waktu deteksi');

            // Review and resolution
            $table->boolean('is_reviewed')->default(false)->comment('Sudah direview admin');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->comment('FK ke users (reviewer)');
            $table->timestamp('reviewed_at')->nullable()->comment('Waktu review');
            $table->text('review_notes')->nullable()->comment('Catatan review');
            $table->enum('resolution', ['false_positive', 'confirmed_anomaly', 'corrected', 'ignored'])->nullable()->comment('Hasil review');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('attendance_id');
            $table->index('student_id');
            $table->index('date');
            $table->index('anomaly_type');
            $table->index('severity');
            $table->index('is_reviewed');
            $table->index('detected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_anomalies');
    }
};
