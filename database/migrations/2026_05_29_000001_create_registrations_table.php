<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('team_name');
            $table->string('competition'); // web-development
            $table->unsignedTinyInteger('member_count')->default(2); // 2-3
            $table->string('institution'); // asal sekolah/kampus
            $table->string('phone'); // no. HP ketua tim
            $table->string('category'); // sma | mahasiswa | umum
            // Status: pending_payment | pending_verification | verified | rejected
            $table->string('status')->default('pending_payment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
