<?php
// database/migrations/xxxx_create_checkouts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone');
            $table->string('country');
            $table->text('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('state')->nullable();
            
            // Shipping
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_price', 10, 2)->default(0);
            
            // Totals
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            // Payment
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'])->default('draft');
            $table->string('tracking_number')->nullable();
            $table->text('label_url')->nullable();
            $table->timestamp('shipped_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};