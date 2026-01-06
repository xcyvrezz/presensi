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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->comment('FK ke users (siswa)');
            $table->foreignId('class_id')->constrained('classes')->comment('FK ke classes');
            $table->string('nis', 20)->unique()->comment('Nomor Induk Siswa');
            $table->string('nisn', 20)->unique()->nullable()->comment('Nomor Induk Siswa Nasional');
            $table->string('card_uid', 50)->unique()->nullable()->comment('UID kartu MIFARE (Physical)');
            $table->string('full_name', 150)->comment('Nama lengkap siswa');
            $table->string('nickname', 50)->nullable()->comment('Nama panggilan');
            $table->enum('gender', ['L', 'P'])->comment('Jenis kelamin: L=Laki-laki, P=Perempuan');
            $table->date('birth_date')->nullable()->comment('Tanggal lahir');
            $table->string('birth_place', 100)->nullable()->comment('Tempat lahir');
            $table->text('address')->nullable()->comment('Alamat lengkap');
            $table->string('phone', 20)->nullable()->comment('Nomor HP siswa');
            $table->string('parent_phone', 20)->nullable()->comment('Nomor HP orang tua/wali');
            $table->string('parent_name', 150)->nullable()->comment('Nama orang tua/wali');
            $table->string('photo')->nullable()->comment('Path foto siswa');
            $table->boolean('nfc_enabled')->default(false)->comment('Apakah NFC smartphone aktif');
            $table->decimal('home_latitude', 10, 8)->nullable()->comment('Latitude rumah (untuk referensi)');
            $table->decimal('home_longitude', 11, 8)->nullable()->comment('Longitude rumah (untuk referensi)');
            $table->boolean('is_active')->default(true)->comment('Status aktif/nonaktif');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('class_id');
            $table->index('nis');
            $table->index('nisn');
            $table->index('card_uid');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
