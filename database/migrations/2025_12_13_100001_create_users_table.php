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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->comment('FK ke roles table');
            $table->string('name', 100)->comment('Nama lengkap user');
            $table->string('email')->unique()->comment('Email untuk login');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->comment('Hashed password');
            $table->string('phone', 20)->nullable()->comment('Nomor telepon/WhatsApp');
            $table->string('photo')->nullable()->comment('Path foto profil');
            $table->boolean('is_active')->default(true)->comment('Status aktif/nonaktif');
            $table->timestamp('last_login_at')->nullable()->comment('Terakhir login');
            $table->string('last_login_ip', 45)->nullable()->comment('IP terakhir login');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('role_id');
            $table->index('email');
            $table->index('is_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
