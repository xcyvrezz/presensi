<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('User yang melakukan aksi');

            // Activity details
            $table->string('action', 100)->comment('Jenis aksi: login, logout, create, update, delete, view, export, etc');
            $table->string('description')->comment('Deskripsi aktivitas');
            $table->string('subject_type', 100)->nullable()->comment('Model yang diubah (User, Student, Attendance, etc)');
            $table->unsignedBigInteger('subject_id')->nullable()->comment('ID record yang diubah');

            // Request details
            $table->string('ip_address', 45)->nullable()->comment('IP address pengguna');
            $table->string('user_agent')->nullable()->comment('Browser/device info');
            $table->string('method', 10)->nullable()->comment('HTTP method: GET, POST, PUT, DELETE');
            $table->string('url')->nullable()->comment('URL endpoint');

            // Change tracking
            $table->json('properties')->nullable()->comment('Data before/after changes');
            $table->json('request_data')->nullable()->comment('Request payload (sensitive data filtered)');

            // Categorization
            $table->enum('category', [
                'authentication', 'user_management', 'student_management',
                'attendance', 'approval', 'settings', 'report', 'system'
            ])->default('system')->comment('Kategori aktivitas');

            $table->enum('severity', ['info', 'warning', 'critical'])->default('info')->comment('Tingkat kepentingan');

            // Session tracking
            $table->string('session_id')->nullable()->comment('Session ID');

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('action');
            $table->index('category');
            $table->index('severity');
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
