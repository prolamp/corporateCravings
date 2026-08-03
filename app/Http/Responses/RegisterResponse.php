<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): Response
    {
        $home = route(user_dashboard_route($request->user()));

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect()->intended($home);
    }
}
