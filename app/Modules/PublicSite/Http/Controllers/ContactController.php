<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Http\Requests\StoreContactRequest;
use App\Modules\PublicSite\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contact,
    ) {}

    public function create(): Response
    {
        return Inertia::render('public/contact', $this->contact->payload());
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        try {
            $this->contact->submit($request->validated());
        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['email' => $e->getMessage()]);
        }

        return redirect()
            ->route('contact')
            ->with('success', true)
            ->with('message', 'Thanks — we received your message and will get back to you soon.')
            ->with('code', 'contact_sent');
    }
}
