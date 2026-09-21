<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(1);
            $table->string('name');
            $table->unsignedBigInteger('amount'); // Integer Rupiah
            $table->unsignedInteger('percentage')->default(0);
            $table->string('status')->default('draft');
            $table->date('due_date')->nullable();
            $table->boolean('is_retention')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
