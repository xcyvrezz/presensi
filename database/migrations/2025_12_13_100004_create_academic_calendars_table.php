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
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->comment('FK ke semesters (nullable untuk event lintas semester)');
            $table->string('title', 200)->comment('Judul event/libur');
            $table->text('description')->nullable()->comment('Deskripsi detail');
            $table->date('start_date')->comment('Tanggal mulai');
            $table->date('end_date')->comment('Tanggal selesai');
            $table->enum('type', ['holiday', 'event', 'exam', 'other'])->default('event')->comment('Tipe: libur, event, ujian, lainnya');
            $table->boolean('is_holiday')->default(false)->comment('Apakah hari libur (tidak ada absensi)');
            $table->string('color', 7)->default('#3B82F6')->comment('Warna untuk kalender UI (hex)');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('semester_id');
            $table->index(['start_date', 'end_date']);
            $table->index('type');
            $table->index('is_holiday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_calendars');
    }
};
