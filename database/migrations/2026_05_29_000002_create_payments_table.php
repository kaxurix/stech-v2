<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->string('proof_file_path');       // path di storage
            $table->string('original_filename');     // nama asli file
            $table->unsignedBigInteger('file_size'); // bytes
            $table->timestamp('uploaded_at')->nullable();
            // Status: pending | approved | rejected
            $table->string('status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable(); // catatan penolakan/approval
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
