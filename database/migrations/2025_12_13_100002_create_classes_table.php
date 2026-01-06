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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->comment('FK ke departments');
            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->comment('FK ke users (wali kelas)');
            $table->string('name', 50)->comment('Nama kelas: X PPLG 1, XI AKL 2, etc');
            $table->integer('grade')->comment('Tingkat kelas: 10, 11, 12');
            $table->string('academic_year', 9)->comment('Tahun ajaran: 2024/2025');
            $table->integer('capacity')->default(36)->comment('Kapasitas maksimal siswa');
            $table->integer('current_students')->default(0)->comment('Jumlah siswa saat ini');
            $table->text('description')->nullable()->comment('Deskripsi atau catatan kelas');
            $table->boolean('is_active')->default(true)->comment('Status aktif/nonaktif');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('department_id');
            $table->index('wali_kelas_id');
            $table->index(['grade', 'academic_year']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
