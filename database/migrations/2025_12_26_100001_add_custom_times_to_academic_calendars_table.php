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
        Schema::table('academic_calendars', function (Blueprint $table) {
            // Custom attendance times (nullable - use default if null)
            $table->time('custom_check_in_start')->nullable()->after('color')->comment('Jam mulai absen masuk khusus (override default)');
            $table->time('custom_check_in_end')->nullable()->after('custom_check_in_start')->comment('Jam akhir absen masuk khusus');
            $table->time('custom_check_in_normal')->nullable()->after('custom_check_in_end')->comment('Jam normal masuk khusus (untuk hitung terlambat)');
            $table->time('custom_check_out_start')->nullable()->after('custom_check_in_normal')->comment('Jam mulai absen pulang khusus');
            $table->time('custom_check_out_end')->nullable()->after('custom_check_out_start')->comment('Jam akhir absen pulang khusus');
            $table->time('custom_check_out_normal')->nullable()->after('custom_check_out_end')->comment('Jam normal pulang khusus (untuk hitung pulang cepat)');

            // Flag untuk menggunakan jam khusus
            $table->boolean('use_custom_times')->default(false)->after('custom_check_out_normal')->comment('Gunakan jam khusus atau default');

            // Affected departments/classes (JSON - null means all)
            $table->json('affected_departments')->nullable()->after('use_custom_times')->comment('Jurusan yang terdampak (null = semua)');
            $table->json('affected_classes')->nullable()->after('affected_departments')->comment('Kelas yang terdampak (null = semua)');

            // Additional info
            $table->foreignId('created_by')->nullable()->after('affected_classes')->constrained('users')->comment('Dibuat oleh user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_calendars', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'custom_check_in_start',
                'custom_check_in_end',
                'custom_check_in_normal',
                'custom_check_out_start',
                'custom_check_out_end',
                'custom_check_out_normal',
                'use_custom_times',
                'affected_departments',
                'affected_classes',
                'created_by',
            ]);
        });
    }
};
