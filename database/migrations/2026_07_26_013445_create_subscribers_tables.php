<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Concerns\AddsCheckConstraints;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriber contracts — company/contact live on users, never duplicated here.
 * package_amount / discount / agreed_total are private (admin + that subscriber).
 */
return new class extends Migration
{
    use AddsCheckConstraints;

    public function up(): void
    {
        Schema::create('subscribe_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->string('meal_type', 32)->default('lunch');
            $table->json('days_of_week');
            $table->unsignedInteger('persons')->default(1);
            $table->time('delivery_time')->nullable();
            $table->decimal('package_amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('agreed_total', 12, 2);
            $table->string('status', 32)->default('active');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'package_id']);
            $table->index(['starts_on', 'ends_on']);
        });

        Schema::create('subscriber_package_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscribe_contract_id')->constrained('subscribe_contracts')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_title')->nullable();
            $table->decimal('package_amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('agreed_total', 12, 2);
            $table->unsignedInteger('persons')->default(1);
            $table->timestamp('effective_from');
            $table->timestamp('effective_to')->nullable();
            $table->timestamps();

            $table->index(['subscribe_contract_id', 'effective_from'], 'sph_contract_effective_idx');
        });

        Schema::create('subscriber_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscribe_contract_id')->constrained('subscribe_contracts')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dish_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('week_of_month')->nullable();
            $table->string('dish_title')->nullable();
            $table->string('package_title')->nullable();
            $table->unsignedInteger('persons');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('status', 32)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subscribe_contract_id', 'date']);
            $table->index(['date', 'status']);
            $table->index(['subscribe_contract_id', 'status']);
        });
        $this->check('subscribe_contracts', 'subscribe_contracts_meal_type_check', 'meal_type IN (\'breakfast\', \'lunch\', \'dinner\')');
        $this->check('subscribe_contracts', 'subscribe_contracts_persons_check', 'persons >= 1');
        $this->check('subscribe_contracts', 'subscribe_contracts_package_amount_check', 'package_amount >= 0');
        $this->check('subscribe_contracts', 'subscribe_contracts_discount_check', 'discount >= 0');
        $this->check('subscribe_contracts', 'subscribe_contracts_agreed_total_check', 'agreed_total >= 0');
        $this->check('subscriber_schedules', 'subscriber_schedules_persons_check', 'persons >= 1');
        $this->check('subscriber_schedules', 'subscriber_schedules_unit_price_check', 'unit_price >= 0');
        $this->check('subscriber_schedules', 'subscriber_schedules_total_check', 'total >= 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_schedules');
        Schema::dropIfExists('subscriber_package_history');
        Schema::dropIfExists('subscribe_contracts');
    }
};
