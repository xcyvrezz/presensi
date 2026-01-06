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
        Schema::create('attendance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->comment('FK ke users (pembuat report)');
            $table->string('report_name', 200)->comment('Nama laporan');
            $table->enum('report_type', [
                'daily', 'weekly', 'monthly', 'semester',
                'student', 'class', 'department', 'violation',
                'custom'
            ])->comment('Tipe laporan');

            // Filter parameters (stored as JSON)
            $table->json('filters')->nullable()->comment('Filter yang digunakan (JSON)');
            $table->date('start_date')->nullable()->comment('Tanggal mulai periode');
            $table->date('end_date')->nullable()->comment('Tanggal akhir periode');
            $table->foreignId('student_id')->nullable()->constrained('students')->comment('FK ke students (untuk report per siswa)');
            $table->foreignId('class_id')->nullable()->constrained('classes')->comment('FK ke classes (untuk report per kelas)');
            $table->foreignId('department_id')->nullable()->constrained('departments')->comment('FK ke departments (untuk report per jurusan)');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->comment('FK ke semesters');

            // Report output
            $table->enum('format', ['pdf', 'excel', 'csv'])->default('pdf')->comment('Format output');
            $table->string('file_path')->nullable()->comment('Path file hasil generate');
            $table->integer('file_size')->nullable()->comment('Ukuran file (bytes)');

            // Statistics (cached for performance)
            $table->json('statistics')->nullable()->comment('Statistik hasil (JSON: total_hadir, total_alpha, dll)');

            // Status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->comment('Status generate');
            $table->text('error_message')->nullable()->comment('Error message jika gagal');
            $table->timestamp('completed_at')->nullable()->comment('Waktu selesai generate');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('generated_by');
            $table->index('report_type');
            $table->index(['start_date', 'end_date']);
            $table->index('student_id');
            $table->index('class_id');
            $table->index('department_id');
            $table->index('semester_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_reports');
    }
};
