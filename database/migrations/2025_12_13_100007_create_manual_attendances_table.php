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
        Schema::create('manual_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('cascade')->comment('FK ke attendances (jika sudah ada record)');
            $table->foreignId('student_id')->constrained('students')->comment('FK ke students');
            $table->date('date')->comment('Tanggal absensi manual');
            $table->enum('type', ['check_in', 'check_out', 'full_day'])->comment('Tipe input manual');
            $table->enum('status', ['izin', 'sakit', 'dispensasi', 'hadir', 'alpha'])->comment('Status yang diinput');
            $table->text('reason')->nullable()->comment('Alasan izin/sakit/dispensasi');
            $table->string('evidence_file')->nullable()->comment('File bukti (surat izin, surat sakit, dll)');
            $table->time('time')->nullable()->comment('Waktu check-in/out (jika type bukan full_day)');

            // Requestor info
            $table->foreignId('requested_by')->constrained('users')->comment('FK ke users (yang mengajukan)');
            $table->timestamp('requested_at')->comment('Waktu pengajuan');
            $table->text('request_notes')->nullable()->comment('Catatan dari pengaju');

            // Approval workflow
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Status approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('FK ke users (yang approve)');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval');
            $table->text('approval_notes')->nullable()->comment('Catatan approval/rejection');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('attendance_id');
            $table->index('student_id');
            $table->index('date');
            $table->index('approval_status');
            $table->index('requested_by');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_attendances');
    }
};
