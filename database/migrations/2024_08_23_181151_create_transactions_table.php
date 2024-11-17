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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payable_id'); // ID of supplier, wholesaler, or customer
            $table->string('payable_type'); // Class name (e.g., Supplier, Wholesaler, Customer)
            $table->integer('amount');
            $table->enum('transaction_type', ['purchase', 'sell']);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payable_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
