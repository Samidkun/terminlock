<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bast_certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('signoff_request_id')->constrained('signoff_requests')->cascadeOnDelete();
            $table->foreignUuid('milestone_id')->constrained('milestones')->cascadeOnDelete();
            $table->string('bast_number')->unique();
            $table->json('snapshot_data');
            $table->string('sha256_checksum', 64);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bast_certificates');
    }
};
