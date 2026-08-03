<?php

use App\Support\Concerns\AddsCheckConstraints;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use AddsCheckConstraints;

    public function up(): void
    {
        Schema::create('subscriber_bill_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscribe_contract_id')->constrained('subscribe_contracts')->cascadeOnDelete();
            $table->string('slip_number', 32)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('adjustment', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status', 32)->default('issued');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subscribe_contract_id', 'status']);
            $table->index(['status', 'period_start', 'period_end']);
        });

        Schema::create('subscriber_bill_slip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_bill_slip_id')->constrained('subscriber_bill_slips')->cascadeOnDelete();
            $table->foreignId('subscriber_schedule_id')->constrained('subscriber_schedules')->restrictOnDelete();
            $table->date('date');
            $table->string('dish_title')->nullable();
            $table->unsignedInteger('persons');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            // App enforces at most one non-cancelled slip per schedule; cancelled slips keep history.
            $table->index('subscriber_schedule_id');
            $table->index(['subscriber_bill_slip_id', 'date']);
        });

        Schema::table('subscriber_schedules', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('unpaid')->after('status');
            $table->foreignId('subscriber_bill_slip_id')
                ->nullable()
                ->after('payment_status')
                ->constrained('subscriber_bill_slips')
                ->nullOnDelete();
            $table->index(['payment_status', 'date']);
        });

        $this->check(
            'subscriber_bill_slips',
            'subscriber_bill_slips_status_check',
            "status IN ('draft', 'issued', 'paid', 'cancelled')"
        );
        $this->check(
            'subscriber_schedules',
            'subscriber_schedules_payment_status_check',
            "payment_status IN ('unpaid', 'paid')"
        );
    }

    public function down(): void
    {
        Schema::table('subscriber_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscriber_bill_slip_id');
            $table->dropColumn('payment_status');
        });

        Schema::dropIfExists('subscriber_bill_slip_items');
        Schema::dropIfExists('subscriber_bill_slips');
    }
};
