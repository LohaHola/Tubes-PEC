<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_product_features', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['promo', 'recommendation']);
            $table->string('section_title')->nullable();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('item_title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('promo_price', 14, 2)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_product_features');
    }
};
