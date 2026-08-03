<?php

namespace App\Modules\PublicSite\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PublicSite\Services\FaqService;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __construct(
        private readonly FaqService $faq,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('public/faq', $this->faq->payload());
    }
}
