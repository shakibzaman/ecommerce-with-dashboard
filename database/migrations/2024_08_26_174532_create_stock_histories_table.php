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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->string('store_id');
            $table->string('quantity');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('invoice_product_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('previous_qty');
            $table->string('update_qty');
            $table->text('note');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
