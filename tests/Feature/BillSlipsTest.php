<?php

use App\Models\User;
use App\Modules\Orders\Models\Order;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Modules\Subscribers\Services\BillSlipService;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function billSlipAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    return $user;
}

function billSlipSubscriber(): User
{
    $user = User::factory()->create([
        'email' => 'bill-sub@example.com',
        'company_name' => 'Bill Co',
    ]);
    $user->assignRole(UserRole::Subscriber->value);

    return $user;
}

test('admin can create bill slip from delivered schedule days', function () {
    $admin = billSlipAdmin();
    $subscriber = billSlipSubscriber();
    $contract = SubscribeContract::factory()->forUser($subscriber)->create(['status' => 'active']);

    $s1 = SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->subDays(3)->toDateString(),
        'total' => 5000,
        'unit_price' => 250,
        'persons' => 20,
    ]);
    $s2 = SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->subDays(2)->toDateString(),
        'total' => 5000,
        'unit_price' => 250,
        'persons' => 20,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.bill-slips.store'), [
            'subscribe_contract_id' => $contract->id,
            'schedule_ids' => [$s1->id, $s2->id],
            'adjustment' => -500,
            'notes' => 'July bill',
        ])
        ->assertRedirect();

    $slip = SubscriberBillSlip::query()->first();
    expect($slip)->not->toBeNull()
        ->and($slip->status)->toBe('issued')
        ->and((float) $slip->subtotal)->toBe(10000.0)
        ->and((float) $slip->adjustment)->toBe(-500.0)
        ->and((float) $slip->total)->toBe(9500.0)
        ->and($slip->items()->count())->toBe(2);

    expect($s1->fresh()->subscriber_bill_slip_id)->toBe($slip->id)
        ->and($s1->fresh()->payment_status)->toBe('unpaid');
});

test('cannot bill undelivered or already slipped days', function () {
    $admin = billSlipAdmin();
    $subscriber = billSlipSubscriber();
    $contract = SubscribeContract::factory()->forUser($subscriber)->create();

    $scheduled = SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'status' => 'scheduled',
        'date' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.bill-slips.create'))
        ->post(route('admin.bill-slips.store'), [
            'subscribe_contract_id' => $contract->id,
            'schedule_ids' => [$scheduled->id],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('schedule_ids');

    $delivered = SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->subDays(2)->toDateString(),
        'total' => 1000,
    ]);

    app(BillSlipService::class)->create($admin, $contract, [$delivered->id]);

    $this->actingAs($admin)
        ->from(route('admin.bill-slips.create'))
        ->post(route('admin.bill-slips.store'), [
            'subscribe_contract_id' => $contract->id,
            'schedule_ids' => [$delivered->id],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('schedule_ids');
});

test('marking slip paid tags schedules and sets order amount_paid', function () {
    $admin = billSlipAdmin();
    $subscriber = billSlipSubscriber();
    $contract = SubscribeContract::factory()->forUser($subscriber)->create();

    $schedule = SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->subDay()->toDateString(),
        'total' => 8000,
        'unit_price' => 400,
        'persons' => 20,
    ]);

    $order = Order::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'subscriber_schedule_id' => $schedule->id,
        'quoted_amount' => 8000,
        'amount_paid' => null,
    ]);

    $slip = app(BillSlipService::class)->create($admin, $contract, [$schedule->id]);

    $this->actingAs($admin)
        ->patch(route('admin.bill-slips.mark-paid', $slip))
        ->assertRedirect();

    expect($slip->fresh()->status)->toBe('paid')
        ->and($schedule->fresh()->payment_status)->toBe('paid')
        ->and((float) $order->fresh()->amount_paid)->toBe(8000.0);
});

test('subscriber can view own issued slip and customer cannot', function () {
    $admin = billSlipAdmin();
    $subscriber = billSlipSubscriber();
    $other = User::factory()->create();
    $other->assignRole(UserRole::Subscriber->value);
    $customer = User::factory()->create();
    $customer->assignRole(UserRole::Customer->value);

    $contract = SubscribeContract::factory()->forUser($subscriber)->create();
    $schedule = SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->subDay()->toDateString(),
        'total' => 2000,
    ]);

    $slip = app(BillSlipService::class)->create($admin, $contract, [$schedule->id]);

    $this->actingAs($subscriber)
        ->get(route('subscriber.bills'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('subscriber/bills/index')
            ->has('slips.data', 1));

    $this->actingAs($subscriber)
        ->get(route('subscriber.bills.show', $slip))
        ->assertOk();

    $this->actingAs($other)
        ->get(route('subscriber.bills.show', $slip))
        ->assertForbidden();

    $this->actingAs($customer)
        ->get(route('subscriber.bills'))
        ->assertForbidden();
});

test('admin bill slips index renders', function () {
    $admin = billSlipAdmin();

    $this->actingAs($admin)
        ->get(route('admin.bill-slips.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/bill-slips/index'));
});
