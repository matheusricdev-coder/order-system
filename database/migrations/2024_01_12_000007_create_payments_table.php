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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->enum('payment_method', ['credit_card', 'debit_card', 'pix', 'boleto']);
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed', 'refunded'])->default('pending');
            $table->timestamp('processed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
