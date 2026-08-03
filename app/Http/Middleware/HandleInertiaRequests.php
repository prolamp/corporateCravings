<?php

namespace App\Http\Middleware;

use App\Modules\Cms\Services\SiteSettingsService;
use App\Modules\PublicSite\Services\CartService;
use App\Modules\PublicSite\Services\SeoService;
use App\Support\StatusRegistry;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? null,
                    ...$user->rolePayload(),
                ] : null,
            ],
            'site' => fn () => app(SiteSettingsService::class)->publicPayload(),
            'seo' => fn () => app(SeoService::class)->sharedDefaults(),
            'cart' => fn () => app(CartService::class)->summary(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'message' => fn () => $request->session()->get('message'),
                'code' => fn () => $request->session()->get('code'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'statuses' => [
                'order' => StatusRegistry::all('order'),
                'schedule' => StatusRegistry::all('schedule'),
                'user' => StatusRegistry::all('user'),
                'generic' => StatusRegistry::all('generic'),
                'expense' => StatusRegistry::all('expense'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
