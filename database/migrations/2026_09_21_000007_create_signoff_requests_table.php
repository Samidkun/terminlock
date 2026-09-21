<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signoff_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('milestone_id')->constrained('milestones')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->string('status')->default('pending'); // pending, signed, rejected, expired
            $table->string('signatory_name')->nullable();
            $table->string('signatory_title')->nullable();
            $table->string('client_ip')->nullable();
            $table->text('client_user_agent')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signoff_requests');
    }
};
