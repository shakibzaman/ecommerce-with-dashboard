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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->string('invoice_type');
            $table->string('store_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('payable_amount', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->decimal('due', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by');
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
