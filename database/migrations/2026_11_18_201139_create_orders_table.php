<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('order_number')->unique();

            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('recipient_address');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');
            $table->text('notes')->nullable();

            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'expired',
                'cancelled',
                'refunded',
            ])->default('pending');

            $table->enum('fulfillment_status', [
                'unprocessed',
                'processing',
                'shipped',
                'completed',
                'cancelled',
            ])->default('unprocessed');

            $table->enum('inventory_status', [
                'reserved',
                'committed',
                'released',
            ])->default('reserved');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
