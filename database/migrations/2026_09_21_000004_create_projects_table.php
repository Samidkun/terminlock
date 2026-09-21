<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('contract_number');
            $table->unsignedBigInteger('total_amount'); // Rupiah integer
            $table->unsignedInteger('retention_percentage')->default(0);
            $table->unsignedInteger('retention_days')->default(180);
            $table->string('status')->default('active'); // active, completed, archived
            $table->timestamps();

            $table->unique(['workspace_id', 'contract_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
