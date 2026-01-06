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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Permission name: users.view, students.create, etc');
            $table->string('display_name', 150)->comment('Display name untuk UI');
            $table->string('group', 50)->comment('Group: users, students, attendance, reports, settings');
            $table->text('description')->nullable()->comment('Deskripsi permission');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('name');
            $table->index('group');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
