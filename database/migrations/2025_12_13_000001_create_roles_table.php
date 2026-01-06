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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Role name: admin, kepala_sekolah, wali_kelas, siswa');
            $table->string('display_name', 100)->comment('Display name untuk UI');
            $table->text('description')->nullable()->comment('Deskripsi role');
            $table->boolean('is_active')->default(true)->comment('Status active/inactive');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
