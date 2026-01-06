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
        Schema::create('attendance_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_attendance_id')->nullable()->constrained('manual_attendances')->onDelete('cascade')->comment('FK ke manual_attendances');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('cascade')->comment('FK ke attendances (untuk edit/koreksi)');
            $table->enum('approval_type', ['manual_attendance', 'edit_attendance', 'delete_attendance', 'bulk_import'])->comment('Tipe approval');
            $table->text('request_data')->nullable()->comment('Data yang diminta (JSON)');
            $table->text('request_reason')->nullable()->comment('Alasan request');

            // Requestor
            $table->foreignId('requested_by')->constrained('users')->comment('FK ke users (pengaju)');
            $table->timestamp('requested_at')->comment('Waktu pengajuan');

            // Approver
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('FK ke users (approver)');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval/rejection');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Status approval');
            $table->text('approval_notes')->nullable()->comment('Catatan approval/rejection');

            // Workflow tracking
            $table->integer('approval_level')->default(1)->comment('Level approval saat ini (1=Wali Kelas, 2=Kepala Sekolah)');
            $table->boolean('requires_multi_approval')->default(false)->comment('Butuh approval bertingkat');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('manual_attendance_id');
            $table->index('attendance_id');
            $table->index('requested_by');
            $table->index('approved_by');
            $table->index('status');
            $table->index('approval_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_approvals');
    }
};
