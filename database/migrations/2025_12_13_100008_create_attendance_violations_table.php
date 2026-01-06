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
        Schema::create('attendance_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->comment('FK ke students');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->comment('FK ke attendances (jika terkait absensi tertentu)');
            $table->foreignId('semester_id')->constrained('semesters')->comment('FK ke semesters');
            $table->date('violation_date')->comment('Tanggal pelanggaran');
            $table->enum('type', [
                'alpha', 'terlambat', 'pulang_cepat', 'bolos',
                'tidak_checkin', 'tidak_checkout', 'gps_invalid', 'other'
            ])->comment('Tipe pelanggaran');
            $table->integer('points')->default(0)->comment('Poin pelanggaran (untuk sistem sanksi)');
            $table->text('description')->nullable()->comment('Deskripsi pelanggaran');
            $table->text('evidence')->nullable()->comment('Bukti/data pendukung (JSON)');

            // Sanction info
            $table->enum('sanction_level', ['warning', 'mild', 'medium', 'severe'])->nullable()->comment('Level sanksi');
            $table->text('sanction_notes')->nullable()->comment('Catatan sanksi yang diberikan');
            $table->foreignId('sanctioned_by')->nullable()->constrained('users')->comment('FK ke users (pemberi sanksi)');
            $table->timestamp('sanctioned_at')->nullable()->comment('Waktu pemberian sanksi');

            // Follow-up
            $table->boolean('is_resolved')->default(false)->comment('Apakah sudah diselesaikan');
            $table->timestamp('resolved_at')->nullable()->comment('Waktu penyelesaian');
            $table->text('resolution_notes')->nullable()->comment('Catatan penyelesaian');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('student_id');
            $table->index('attendance_id');
            $table->index('semester_id');
            $table->index('violation_date');
            $table->index('type');
            $table->index('is_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_violations');
    }
};
