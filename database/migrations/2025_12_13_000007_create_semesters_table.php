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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Nama semester: Semester 1 2024/2025');
            $table->string('academic_year', 9)->comment('Tahun ajaran: 2024/2025');
            $table->integer('semester')->comment('Semester ke: 1 atau 2');
            $table->date('start_date')->comment('Tanggal mulai semester');
            $table->date('end_date')->comment('Tanggal akhir semester');
            $table->boolean('is_active')->default(false)->comment('Apakah semester aktif saat ini');
            $table->text('description')->nullable()->comment('Deskripsi atau catatan');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('academic_year');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
