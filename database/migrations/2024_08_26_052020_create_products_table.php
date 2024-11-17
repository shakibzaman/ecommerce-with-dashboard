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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('unit_id');
            $table->string('cold_storage_quantity')->default(0);
            $table->string('office_quantity')->default(0);
            $table->decimal('price', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('wholesell_price', 20, 2)->default(0);
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
