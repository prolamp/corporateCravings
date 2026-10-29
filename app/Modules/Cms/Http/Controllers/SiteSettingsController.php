<?php

namespace App\Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Http\Requests\UpdateSiteSettingsRequest;
use App\Modules\Cms\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    public function __construct(
        private readonly SiteSettingsService $settings,
    ) {}

    public function edit(Request $request): Response
    {
        abort_unless($request->user()?->can('settings.manage'), 403);

        return Inertia::render('admin/settings/index', $this->settings->formPayload());
    }

    public function update(UpdateSiteSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->settings->update(
            $validated['values'] ?? [],
            $request->file('signature'),
            (bool) ($validated['remove_signature'] ?? false),
        );

        return back()
            ->with('success', true)
            ->with('message', 'Site settings saved.')
            ->with('code', 'site_settings_updated');
    }
}
