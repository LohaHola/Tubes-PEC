<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('provider');
            $table->string('provider_order_id')->nullable();
            $table->string('provider_transaction_id')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('snap_token')->nullable();
            $table->text('redirect_url')->nullable();
            $table->text('qr_code_url')->nullable();

            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('IDR');

            $table->string('status')->default('pending');
            $table->string('provider_status')->nullable();
            $table->string('fraud_status')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'provider_order_id']);
            $table->index(['provider', 'provider_transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};