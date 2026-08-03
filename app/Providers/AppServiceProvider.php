<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\User;
use App\Modules\Addons\Models\Addon;
use App\Modules\Addons\Policies\AddonPolicy;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Policies\CategoryPolicy;
use App\Modules\Catalog\Policies\DishPolicy;
use App\Modules\Cms\Policies\PagePolicy;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Delivery\Policies\DeliveryZonePolicy;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Expenses\Policies\ExpenseCategoryPolicy;
use App\Modules\Expenses\Policies\ExpensePolicy;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Policies\OrderPolicy;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Policies\PackagePolicy;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Modules\Subscribers\Policies\SubscribeContractPolicy;
use App\Modules\Subscribers\Policies\SubscriberBillSlipPolicy;
use App\Modules\Subscribers\Policies\SubscriberSchedulePolicy;
use App\Modules\Users\Policies\UserPolicy;
use App\Support\ImageService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Dish::class, DishPolicy::class);
        Gate::policy(Addon::class, AddonPolicy::class);
        Gate::policy(Package::class, PackagePolicy::class);
        Gate::policy(DeliveryZone::class, DeliveryZonePolicy::class);
        Gate::policy(ExpenseCategory::class, ExpenseCategoryPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(SubscribeContract::class, SubscribeContractPolicy::class);
        Gate::policy(SubscriberSchedule::class, SubscriberSchedulePolicy::class);
        Gate::policy(SubscriberBillSlip::class, SubscriberBillSlipPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') ?: $request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('track-order', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
