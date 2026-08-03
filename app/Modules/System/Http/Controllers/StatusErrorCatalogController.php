<?php

namespace App\Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\System\Http\Requests\UpdateErrorMessagesRequest;
use App\Modules\System\Http\Requests\UpdateStatusLabelsRequest;
use App\Modules\System\Services\StatusErrorCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class StatusErrorCatalogController extends Controller
{
    public function __construct(
        private readonly StatusErrorCatalogService $catalog,
    ) {}

    public function edit(Request $request): Response
    {
        abort_unless($request->user()?->can('statuses.manage'), 403);

        return Inertia::render('admin/statuses/index', $this->catalog->catalog());
    }

    public function updateStatuses(UpdateStatusLabelsRequest $request): RedirectResponse
    {
        try {
            $this->catalog->updateStatusLabels($request->validated('labels'));
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', 'Status labels saved.')
            ->with('code', 'status_labels_updated');
    }

    public function updateErrors(UpdateErrorMessagesRequest $request): RedirectResponse
    {
        try {
            $this->catalog->updateErrorMessages($request->validated('messages'));
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', true)
            ->with('message', 'Error messages saved.')
            ->with('code', 'error_messages_updated');
    }
}
