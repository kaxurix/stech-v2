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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position'); // urutan 1-4, posisi 1 = ketua tim
            $table->boolean('is_leader')->default(false);
            $table->string('full_name');
            $table->string('identity_number'); // NIM / NISN
            $table->string('institution');     // asal sekolah/kampus anggota ini
            $table->string('major')->nullable();  // jurusan/program studi
            $table->string('batch')->nullable();  // angkatan/kelas
            $table->string('phone');              // nomor WA aktif
            $table->string('email')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
