<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('milestone_id')->constrained('milestones')->cascadeOnDelete();
            $table->string('invoice_number');
            $table->unsignedBigInteger('amount'); // Integer Rupiah
            $table->date('issued_at');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->text('payment_notes')->nullable();
            $table->string('status')->default('issued'); // issued, paid, overdue
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
