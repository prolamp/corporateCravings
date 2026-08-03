<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Concerns\AddsCheckConstraints;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog: categories → dishes → variations.
 * type: single (cart/COD) | bulk (WhatsApp only). One image per dish.
 */
return new class extends Migration
{
    use AddsCheckConstraints;

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });

        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->string('image')->nullable();
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status', 'sort_order']);
            $table->index(['status', 'sort_order']);
        });

        Schema::create('dish_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 32);
            $table->decimal('price', 12, 2);
            $table->string('type', 16)->default('single');
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['dish_id', 'label']);
            $table->index(['dish_id', 'type', 'status']);
            $table->index(['type', 'status', 'sort_order']);
        });

        $this->check('dish_variations', 'dish_variations_type_check', 'type IN (\'single\', \'bulk\')');
        $this->check('dish_variations', 'dish_variations_quantity_check', 'quantity > 0');
        $this->check('dish_variations', 'dish_variations_price_check', 'price >= 0');
        $this->check('dish_variations', 'dish_variations_unit_check', 'unit IN (\'serving\', \'kg\', \'persons\')');
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_variations');
        Schema::dropIfExists('dishes');
        Schema::dropIfExists('categories');
    }
};
