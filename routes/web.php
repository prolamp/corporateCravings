<?php

use App\Http\Controllers\DashboardController;
use App\Models\User;
use App\Modules\Addons\Http\Controllers\AddonController;
use App\Modules\Catalog\Http\Controllers\CategoryController;
use App\Modules\Catalog\Http\Controllers\DishController;
use App\Modules\Cms\Http\Controllers\PageController;
use App\Modules\Cms\Http\Controllers\SiteSettingsController;
use App\Modules\Delivery\Http\Controllers\DeliveryZoneController;
use App\Modules\Expenses\Http\Controllers\DishCostCalculatorController;
use App\Modules\Expenses\Http\Controllers\ExpenseCategoryController;
use App\Modules\Expenses\Http\Controllers\ExpenseController;
use App\Modules\Orders\Http\Controllers\OrderController;
use App\Modules\Packages\Http\Controllers\PackageController;
use App\Modules\PublicSite\Http\Controllers\AboutController;
use App\Modules\PublicSite\Http\Controllers\BulkOrdersController;
use App\Modules\PublicSite\Http\Controllers\CartController;
use App\Modules\PublicSite\Http\Controllers\CheckoutController;
use App\Modules\PublicSite\Http\Controllers\CmsPageController;
use App\Modules\PublicSite\Http\Controllers\ContactController;
use App\Modules\PublicSite\Http\Controllers\CustomerPortalController;
use App\Modules\PublicSite\Http\Controllers\DishShowController;
use App\Modules\PublicSite\Http\Controllers\FaqController;
use App\Modules\PublicSite\Http\Controllers\HomeController;
use App\Modules\PublicSite\Http\Controllers\MenuController;
use App\Modules\PublicSite\Http\Controllers\PackageShowController;
use App\Modules\PublicSite\Http\Controllers\PackagesController;
use App\Modules\PublicSite\Http\Controllers\SeoController;
use App\Modules\PublicSite\Http\Controllers\SubscriberPortalController;
use App\Modules\PublicSite\Http\Controllers\TrackOrderController;
use App\Modules\Reports\Http\Controllers\ReportController;
use App\Modules\Subscribers\Http\Controllers\BillSlipController;
use App\Modules\Subscribers\Http\Controllers\SubscribeContractController;
use App\Modules\Subscribers\Http\Controllers\SubscriberController;
use App\Modules\Subscribers\Http\Controllers\SubscriberScheduleController;
use App\Modules\System\Http\Controllers\StatusErrorCatalogController;
use App\Modules\Users\Http\Controllers\UserController;
use App\Support\Enums\UserRole;
use Illuminate\Support\Facades\Route;

Route::bind('subscriber', function (string $value): User {
    return User::query()
        ->role(UserRole::Subscriber->value)
        ->whereKey($value)
        ->firstOrFail();
});

Route::get('/', HomeController::class)->name('home');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/about', AboutController::class)->name('about');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');
Route::get('/faq', FaqController::class)->name('faq');
Route::get('/menu', MenuController::class)->name('menu');
Route::get('/menu/{slug}', DishShowController::class)->name('menu.show');
Route::get('/packages', PackagesController::class)->name('packages');
Route::get('/packages/{slug}', PackageShowController::class)->name('packages.show');
Route::get('/bulk-orders', BulkOrdersController::class)->name('bulk-orders');

Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:checkout')
    ->name('checkout.store');
Route::get('/orders/{orderNumber}/success', [CheckoutController::class, 'success'])
    ->name('orders.success');
Route::get('/track-order', TrackOrderController::class)
    ->middleware('throttle:track-order')
    ->name('track-order');

Route::get('/{slug}', CmsPageController::class)
    ->whereIn('slug', ['privacy-policy', 'terms', 'delivery-policy', 'cancellation-refund'])
    ->name('pages.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('subscriber')->name('subscriber.')->group(function () {
        Route::get('dashboard', [SubscriberPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('schedules', [SubscriberPortalController::class, 'schedules'])->name('schedules');
        Route::get('history', [SubscriberPortalController::class, 'history'])->name('history');
        Route::get('contract', [SubscriberPortalController::class, 'contract'])->name('contract');
        Route::get('bills', [SubscriberPortalController::class, 'bills'])->name('bills');
        Route::get('bills/{bill_slip}', [SubscriberPortalController::class, 'billShow'])->name('bills.show');
        Route::get('bills/{bill_slip}/receipt', [SubscriberPortalController::class, 'billReceipt'])->name('bills.receipt');
    });

    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('orders', [CustomerPortalController::class, 'orders'])->name('orders');
        Route::post('orders/{order}/reorder', [CustomerPortalController::class, 'reorder'])
            ->name('orders.reorder');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        Route::resource('users', UserController::class);

        Route::patch('categories/{category}/status', [CategoryController::class, 'toggleStatus'])
            ->name('categories.toggle-status');
        Route::resource('categories', CategoryController::class);

        Route::patch('dishes/{dish}/status', [DishController::class, 'toggleStatus'])
            ->name('dishes.toggle-status');
        Route::resource('dishes', DishController::class);

        Route::patch('addons/{addon}/status', [AddonController::class, 'toggleStatus'])
            ->name('addons.toggle-status');
        Route::resource('addons', AddonController::class);

        Route::patch('packages/{package}/status', [PackageController::class, 'toggleStatus'])
            ->name('packages.toggle-status');
        Route::resource('packages', PackageController::class);

        Route::patch('subscribers/{subscriber}/status', [SubscriberController::class, 'toggleStatus'])
            ->name('subscribers.toggle-status');
        Route::resource('subscribers', SubscriberController::class);

        Route::patch('contracts/{contract}/status', [SubscribeContractController::class, 'toggleStatus'])
            ->name('contracts.toggle-status');
        Route::resource('contracts', SubscribeContractController::class);

        Route::post('schedules/generate', [SubscriberScheduleController::class, 'generate'])
            ->name('schedules.generate');
        Route::post('schedules/shuffle', [SubscriberScheduleController::class, 'shuffle'])
            ->name('schedules.shuffle');
        Route::patch('schedules/{schedule}/deliver', [SubscriberScheduleController::class, 'deliver'])
            ->name('schedules.deliver');
        Route::get('schedules', [SubscriberScheduleController::class, 'index'])
            ->name('schedules.index');
        Route::put('schedules/{schedule}', [SubscriberScheduleController::class, 'update'])
            ->name('schedules.update');
        Route::delete('schedules/{schedule}', [SubscriberScheduleController::class, 'destroy'])
            ->name('schedules.destroy');

        Route::patch('bill-slips/{bill_slip}/mark-paid', [BillSlipController::class, 'markPaid'])
            ->name('bill-slips.mark-paid');
        Route::patch('bill-slips/{bill_slip}/mark-unpaid', [BillSlipController::class, 'markUnpaid'])
            ->name('bill-slips.mark-unpaid');
        Route::patch('bill-slips/{bill_slip}/cancel', [BillSlipController::class, 'cancel'])
            ->name('bill-slips.cancel');
        Route::get('bill-slips/{bill_slip}/receipt', [BillSlipController::class, 'receipt'])
            ->name('bill-slips.receipt');
        Route::resource('bill-slips', BillSlipController::class)->except(['destroy']);

        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('orders.update-status');
        Route::resource('orders', OrderController::class)->except(['destroy']);

        Route::patch('delivery-zones/{delivery_zone}/status', [DeliveryZoneController::class, 'toggleStatus'])
            ->name('delivery-zones.toggle-status');
        Route::resource('delivery-zones', DeliveryZoneController::class);

        Route::patch('expense-categories/{expense_category}/status', [ExpenseCategoryController::class, 'toggleStatus'])
            ->name('expense-categories.toggle-status');
        Route::resource('expense-categories', ExpenseCategoryController::class);

        Route::patch('expenses/{expense}/status', [ExpenseController::class, 'updateStatus'])
            ->name('expenses.update-status');
        Route::resource('expenses', ExpenseController::class);

        Route::get('cost-calculator', [DishCostCalculatorController::class, 'show'])
            ->name('cost-calculator.show');
        Route::post('cost-calculator/estimate', [DishCostCalculatorController::class, 'estimate'])
            ->name('cost-calculator.estimate');

        Route::patch('pages/{page}/status', [PageController::class, 'toggleStatus'])
            ->name('pages.toggle-status');
        Route::resource('pages', PageController::class);

        Route::get('settings', [SiteSettingsController::class, 'edit'])
            ->name('settings.edit');
        Route::put('settings', [SiteSettingsController::class, 'update'])
            ->name('settings.update');

        Route::get('reports', [ReportController::class, 'index'])
            ->name('reports.index');
        Route::get('reports/orders', [ReportController::class, 'orders'])
            ->name('reports.orders');
        Route::get('reports/contracts', [ReportController::class, 'contracts'])
            ->name('reports.contracts');
        Route::get('reports/earnings', [ReportController::class, 'earnings'])
            ->name('reports.earnings');
        Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])
            ->name('reports.profit-loss');

        Route::get('statuses', [StatusErrorCatalogController::class, 'edit'])
            ->name('statuses.edit');
        Route::put('statuses/labels', [StatusErrorCatalogController::class, 'updateStatuses'])
            ->name('statuses.labels.update');
        Route::put('statuses/errors', [StatusErrorCatalogController::class, 'updateErrors'])
            ->name('statuses.errors.update');
    });
});

require __DIR__.'/settings.php';

// Ensures web middleware (Inertia shared props) runs before branded 404.
Route::fallback(fn () => abort(404));
