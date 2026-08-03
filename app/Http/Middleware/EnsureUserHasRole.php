<?php

namespace App\Http\Middleware;

use App\Support\ErrorCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, ErrorCatalog::message('unauthenticated'));
        }

        if ($roles !== [] && ! $user->hasAnyRole($roles)) {
            abort(403, ErrorCatalog::message('unauthorized'));
        }

        return $next($request);
    }
}
