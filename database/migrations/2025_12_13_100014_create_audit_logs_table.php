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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->comment('FK ke users (pelaku aksi)');
            $table->string('user_type', 50)->nullable()->comment('Tipe user: admin, wali_kelas, dll');
            $table->string('ip_address', 45)->nullable()->comment('IP address');
            $table->string('user_agent')->nullable()->comment('User agent browser');

            // Auditable entity
            $table->string('auditable_type', 100)->comment('Model yang diaudit: Student, Attendance, dll');
            $table->unsignedBigInteger('auditable_id')->comment('ID record yang diaudit');

            // Event details
            $table->string('event', 50)->comment('Event: created, updated, deleted, restored');
            $table->json('old_values')->nullable()->comment('Nilai lama (untuk update/delete)');
            $table->json('new_values')->nullable()->comment('Nilai baru (untuk create/update)');

            // Additional context
            $table->string('url')->nullable()->comment('URL request');
            $table->json('tags')->nullable()->comment('Tags untuk filtering (JSON array)');
            $table->text('notes')->nullable()->comment('Catatan tambahan');

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event');
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
