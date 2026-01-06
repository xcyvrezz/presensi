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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Setting key: check_in_start, check_in_end, dll');
            $table->string('group', 50)->comment('Group: time_windows, geofencing, violations, notifications');
            $table->string('label', 150)->comment('Label untuk UI');
            $table->text('description')->nullable()->comment('Deskripsi setting');
            $table->string('value_type', 20)->default('string')->comment('Tipe nilai: string, integer, time, boolean, json');
            $table->text('value')->comment('Nilai setting');
            $table->text('default_value')->nullable()->comment('Nilai default');
            $table->text('validation_rules')->nullable()->comment('Aturan validasi (JSON)');
            $table->boolean('is_editable')->default(true)->comment('Bisa diedit melalui UI');
            $table->integer('display_order')->default(0)->comment('Urutan tampil di UI');
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->comment('FK ke users (terakhir ubah)');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('key');
            $table->index('group');
            $table->index('is_editable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
