<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Concerns\AddsCheckConstraints;
use Illuminate\Support\Facades\Schema;

/**
 * Unified orders for web / mobile / admin across individual, event, subscription.
 * Line items snapshot titles/prices so history survives catalog edits.
 * amount_paid is admin-only (never set from public checkout).
 */
return new class extends Migration
{
    use AddsCheckConstraints;

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32);
            $table->string('channel', 32);
            $table->string('status', 32)->default('pending');
            $table->string('payment_method', 32)->default('cod');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 32)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('delivery_address')->nullable();
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('list_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('quoted_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->nullable();
            $table->foreignId('subscribe_contract_id')->nullable()->constrained('subscribe_contracts')->nullOnDelete();
            $table->foreignId('subscriber_schedule_id')->nullable()->unique()->constrained('subscriber_schedules')->nullOnDelete();
            $table->string('client_platform', 32)->nullable();
            $table->string('app_version', 32)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->string('delivery_person_name')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'channel', 'status']);
            $table->index(['status', 'placed_at']);
            $table->index(['placed_at', 'status']);
            $table->index(['customer_phone', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['subscribe_contract_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dish_variation_id')->nullable()->constrained('dish_variations')->nullOnDelete();
            $table->string('title');
            $table->string('variation_label')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->index(['order_id', 'dish_id']);
        });

        Schema::create('order_item_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
            $table->index(['to_status', 'created_at']);
        });

        $this->check('orders', 'orders_type_check', 'type IN (\'individual\', \'event\', \'subscription\')');
        $this->check('orders', 'orders_channel_check', 'channel IN (\'web\', \'mobile_app\', \'admin\')');
        $this->check('orders', 'orders_delivery_fee_check', 'delivery_fee >= 0');
        $this->check('orders', 'orders_subtotal_check', 'subtotal >= 0');
        $this->check('orders', 'orders_list_amount_check', 'list_amount >= 0');
        $this->check('orders', 'orders_discount_amount_check', 'discount_amount >= 0');
        $this->check('orders', 'orders_discount_percent_check', 'discount_percent >= 0 AND discount_percent <= 100');
        $this->check('orders', 'orders_quoted_amount_check', 'quoted_amount >= 0');
        $this->check('orders', 'orders_amount_paid_check', 'amount_paid IS NULL OR amount_paid >= 0');
        $this->check('order_items', 'order_items_quantity_check', 'quantity >= 1');
        $this->check('order_items', 'order_items_unit_price_check', 'unit_price >= 0');
        $this->check('order_items', 'order_items_line_total_check', 'line_total >= 0');
        $this->check('order_item_addons', 'order_item_addons_quantity_check', 'quantity >= 1');
        $this->check('order_item_addons', 'order_item_addons_unit_price_check', 'unit_price >= 0');
        $this->check('order_item_addons', 'order_item_addons_line_total_check', 'line_total >= 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_item_addons');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
