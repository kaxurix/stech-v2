<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DemoAuth Middleware
 *
 * Guards routes by checking for the `demo_logged_in` session flag that is set
 * by DemoController::login(). Redirects unauthenticated visitors to the
 * landing page with a query-string flag so the login modal auto-opens.
 */
class DemoAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('demo_logged_in')) {
            return redirect()->route('welcome', ['login' => 1]);
        }

        return $next($request);
    }
}
