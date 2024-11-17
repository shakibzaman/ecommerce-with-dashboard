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
            $table->integer('payable_id'); // ID of supplier, wholesaler, or customer
            $table->string('payable_type'); // Class name (e.g., Supplier, Wholesaler, Customer)
            $table->integer('amount');
            $table->enum('payment_type', ['credit', 'debit']); // Credit or Debit
            $table->date('payment_date');
            $table->string('payment_method')->nullable(); // e.g., Cash, Bank Transfer
            $table->text('note')->nullable();
            $table->text('invoice_id')->nullable();
            $table->text('transaction_id')->nullable();
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();
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
