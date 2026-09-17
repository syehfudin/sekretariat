<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
        });

        Schema::create('anggota', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('pekerjaan', 150)->nullable();
            $table->timestamps();
        });

        // Pivot: user (C1/C2) membawahi anggota
        Schema::create('anggota_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['anggota_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
        Schema::dropIfExists('roles');
    }
};