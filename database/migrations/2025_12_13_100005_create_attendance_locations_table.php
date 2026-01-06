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
        Schema::create('attendance_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nama lokasi: Gerbang Utama, Gedung A, Lab PPLG');
            $table->text('description')->nullable()->comment('Deskripsi lokasi');
            $table->decimal('latitude', 10, 8)->comment('Latitude pusat geofencing');
            $table->decimal('longitude', 11, 8)->comment('Longitude pusat geofencing');
            $table->integer('radius')->default(15)->comment('Radius geofencing dalam meter');
            $table->enum('type', ['gate', 'building', 'classroom', 'lab', 'other'])->default('gate')->comment('Tipe lokasi');
            $table->boolean('is_check_in_enabled')->default(true)->comment('Boleh check-in di lokasi ini');
            $table->boolean('is_check_out_enabled')->default(true)->comment('Boleh check-out di lokasi ini');
            $table->boolean('is_active')->default(true)->comment('Status aktif/nonaktif');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_locations');
    }
};
