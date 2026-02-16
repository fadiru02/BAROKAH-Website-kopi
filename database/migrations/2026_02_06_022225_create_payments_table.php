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
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->string('external_id')->unique(); // ID unik dari sistem kita
            $table->string('xendit_id')->nullable();   // ID Invoice dari Xendit (misal: 65a...)
            $table->string('checkout_link');           // URL untuk user bayar
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('PENDING');
            // Detail setelah bayar (terisi via Webhook)
            $table->string('payment_method')->nullable();  // Ewallet, VA, QRIS
            $table->string('payment_channel')->nullable(); // OVO, Mandiri, dll
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
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
