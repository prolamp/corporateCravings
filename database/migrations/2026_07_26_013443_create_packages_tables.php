<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Concerns\AddsCheckConstraints;
use Illuminate\Support\Facades\Schema;

/**
 * Corporate packages: base/person → discount → public total/person.
 * package_items snapshot the per-person dish price used at build time.
 */
return new class extends Migration
{
    use AddsCheckConstraints;

    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->string('image')->nullable();
            $table->decimal('base_amount_per_person', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('total_amount_per_person', 12, 2)->default(0);
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });

        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->restrictOnDelete();
            $table->foreignId('dish_variation_id')->nullable()->constrained('dish_variations')->nullOnDelete();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['package_id', 'dish_id']);
            $table->index(['package_id', 'sort_order']);
        });

        $this->check('packages', 'packages_base_check', 'base_amount_per_person >= 0');
        $this->check('packages', 'packages_discount_amount_check', 'discount_amount >= 0');
        $this->check('packages', 'packages_discount_percent_check', 'discount_percent >= 0 AND discount_percent <= 100');
        $this->check('packages', 'packages_total_check', 'total_amount_per_person >= 0');
        $this->check('package_items', 'package_items_unit_price_check', 'unit_price >= 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');
    }
};
