<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Concerns\AddsCheckConstraints;
use Illuminate\Support\Facades\Schema;

/**
 * Addons (sauces/drinks/extras) + optional dish links.
 * Empty dish_addon + is_global=true ⇒ available on every dish.
 */
return new class extends Migration
{
    use AddsCheckConstraints;

    public function up(): void
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->string('type', 32)->default('extra');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_global')->default(true);
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_global', 'sort_order']);
            $table->index(['type', 'status']);
        });

        Schema::create('dish_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['dish_id', 'addon_id']);
        });

        $this->check('addons', 'addons_type_check', 'type IN (\'sauce\', \'drink\', \'extra\')');
        $this->check('addons', 'addons_price_check', 'price >= 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_addon');
        Schema::dropIfExists('addons');
    }
};
