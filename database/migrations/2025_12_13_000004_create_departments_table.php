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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Kode jurusan: PPLG, AKL, TO');
            $table->string('name', 100)->comment('Nama lengkap jurusan');
            $table->text('description')->nullable()->comment('Deskripsi jurusan');
            $table->string('head_teacher')->nullable()->comment('Nama kepala jurusan');
            $table->string('phone', 20)->nullable()->comment('Nomor telepon jurusan');
            $table->boolean('is_active')->default(true)->comment('Status aktif/nonaktif');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
