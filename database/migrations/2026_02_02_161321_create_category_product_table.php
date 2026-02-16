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
            Schema::create('category_product', function (Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                // Foreign key ke tabel categories
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                // Foreign key ke tabel products
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
