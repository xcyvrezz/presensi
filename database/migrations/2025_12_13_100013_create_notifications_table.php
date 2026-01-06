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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('FK ke users (penerima notifikasi)');
            $table->string('title', 200)->comment('Judul notifikasi');
            $table->text('message')->comment('Isi pesan notifikasi');
            $table->enum('type', [
                'attendance_reminder', 'violation_warning', 'approval_request',
                'approval_result', 'report_ready', 'system_alert', 'other'
            ])->comment('Tipe notifikasi');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->comment('Prioritas notifikasi');
            $table->json('data')->nullable()->comment('Data tambahan (JSON: link, action, dll)');

            // Related entities (polymorphic-like approach)
            $table->string('related_type', 50)->nullable()->comment('Model terkait: Attendance, ManualAttendance, dll');
            $table->unsignedBigInteger('related_id')->nullable()->comment('ID record terkait');

            // Read status
            $table->boolean('is_read')->default(false)->comment('Sudah dibaca');
            $table->timestamp('read_at')->nullable()->comment('Waktu dibaca');

            // Delivery channels
            $table->boolean('sent_push')->default(false)->comment('Sudah dikirim via push notification');
            $table->boolean('sent_email')->default(false)->comment('Sudah dikirim via email');
            $table->timestamp('sent_at')->nullable()->comment('Waktu pengiriman');

            // Action tracking
            $table->string('action_url')->nullable()->comment('URL action button');
            $table->boolean('action_taken')->default(false)->comment('Action sudah diambil');
            $table->timestamp('action_taken_at')->nullable()->comment('Waktu action diambil');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('priority');
            $table->index('is_read');
            $table->index(['related_type', 'related_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
