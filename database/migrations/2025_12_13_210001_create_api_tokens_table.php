<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('User pemilik token');

            // Token details
            $table->string('name')->comment('Nama token untuk identifikasi');
            $table->string('token', 64)->unique()->comment('API token (hashed)');
            $table->text('abilities')->nullable()->comment('Permissions/scopes yang dimiliki token');

            // Usage tracking
            $table->timestamp('last_used_at')->nullable()->comment('Terakhir kali digunakan');
            $table->integer('usage_count')->default(0)->comment('Jumlah penggunaan');
            $table->string('last_ip_address', 45)->nullable()->comment('IP terakhir yang menggunakan');

            // Security
            $table->timestamp('expires_at')->nullable()->comment('Tanggal kadaluarsa');
            $table->boolean('is_active')->default(true)->comment('Status aktif/nonaktif');

            // Rate limiting
            $table->integer('rate_limit')->default(60)->comment('Request per menit');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('token');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
