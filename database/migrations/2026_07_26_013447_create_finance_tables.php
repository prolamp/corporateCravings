<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Concerns\AddsCheckConstraints;
use Illuminate\Support\Facades\Schema;

/**
 * Finance: expense categories + expenses only.
 * Grocery expenses supply unit prices to the dish cost calculator (UI/math only).
 */
return new class extends Migration
{
    use AddsCheckConstraints;

    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 32)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->date('date');
            $table->string('status', 32)->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['date', 'status']);
            $table->index(['expense_category_id', 'status']);
            $table->index(['expense_category_id', 'title']);
            $table->index(['created_by', 'date']);
        });

        $this->check('expenses', 'expenses_quantity_check', 'quantity > 0');
        $this->check('expenses', 'expenses_unit_price_check', 'unit_price >= 0');
        $this->check('expenses', 'expenses_total_check', 'total >= 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
