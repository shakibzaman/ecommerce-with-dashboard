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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('store_id');
            $table->string('address');
            $table->string('total');
            $table->string('discount');
            $table->string('payable_amount');
            $table->string('paid');
            $table->string('due');
            $table->string('status_id');
            $table->string('delivery_charge')->default(0);
            $table->string('delivery_company_id');
            $table->string('note')->nullable();
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
