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
        Schema::create('product_transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('quantity');
            $table->string('transfer_from');
            $table->string('transfer_to');
            $table->integer('transfer_pre_quantity');
            $table->integer('transfer_post_quantity');
            $table->integer('received_pre_quantity');
            $table->integer('received_post_quantity');
            $table->integer('transfer_by');
            $table->string('reason');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transfer_logs');
    }
};
