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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->comment('FK ke students');
            $table->foreignId('class_id')->constrained('classes')->comment('FK ke classes');
            $table->foreignId('semester_id')->constrained('semesters')->comment('FK ke semesters');
            $table->date('date')->comment('Tanggal absensi');

            // Check-in data
            $table->time('check_in_time')->nullable()->comment('Waktu check-in');
            $table->enum('check_in_method', ['rfid_physical', 'nfc_mobile', 'manual', 'system'])->nullable()->comment('Metode check-in');
            $table->foreignId('check_in_location_id')->nullable()->constrained('attendance_locations')->comment('FK ke attendance_locations (check-in)');
            $table->decimal('check_in_latitude', 10, 8)->nullable()->comment('Latitude check-in (mobile)');
            $table->decimal('check_in_longitude', 11, 8)->nullable()->comment('Longitude check-in (mobile)');
            $table->integer('check_in_distance')->nullable()->comment('Jarak dari lokasi (meter)');
            $table->string('check_in_photo')->nullable()->comment('Foto saat check-in (opsional)');

            // Check-out data
            $table->time('check_out_time')->nullable()->comment('Waktu check-out');
            $table->enum('check_out_method', ['rfid_physical', 'nfc_mobile', 'manual', 'system'])->nullable()->comment('Metode check-out');
            $table->foreignId('check_out_location_id')->nullable()->constrained('attendance_locations')->comment('FK ke attendance_locations (check-out)');
            $table->decimal('check_out_latitude', 10, 8)->nullable()->comment('Latitude check-out (mobile)');
            $table->decimal('check_out_longitude', 11, 8)->nullable()->comment('Longitude check-out (mobile)');
            $table->integer('check_out_distance')->nullable()->comment('Jarak dari lokasi (meter)');
            $table->string('check_out_photo')->nullable()->comment('Foto saat check-out (opsional)');

            // Status and calculation
            $table->enum('status', [
                'hadir', 'alpha', 'izin', 'sakit', 'dispensasi',
                'terlambat', 'pulang_cepat', 'bolos',
                'izin_terlambat', 'izin_pulang_cepat', 'libur'
            ])->default('alpha')->comment('Status kehadiran final');
            $table->integer('late_minutes')->default(0)->comment('Menit terlambat');
            $table->integer('early_leave_minutes')->default(0)->comment('Menit pulang lebih awal');
            $table->decimal('percentage', 5, 2)->default(0)->comment('Persentase kehadiran (weighted)');

            // Approval and notes
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('FK ke users (yang approve)');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->comment('Status approval (untuk manual/izin)');

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: satu siswa hanya punya 1 record per tanggal
            $table->unique(['student_id', 'date']);

            // Indexes
            $table->index('student_id');
            $table->index('class_id');
            $table->index('semester_id');
            $table->index('date');
            $table->index('status');
            $table->index(['student_id', 'date']);
            $table->index('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
