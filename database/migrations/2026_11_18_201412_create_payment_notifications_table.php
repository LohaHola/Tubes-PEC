<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('notification_key')->unique();
            $table->string('provider_transaction_id')->nullable();

            $table->string('transaction_status')->nullable();
            $table->string('fraud_status')->nullable();

            $table->boolean('signature_valid')->default(false);
            $table->string('processing_status')->default('received');

            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index('provider_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notifications');
    }
};