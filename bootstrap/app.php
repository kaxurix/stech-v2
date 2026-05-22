<?php
// ──────────────────────────────────────────────────────────────────────────────
// bootstrap/app.php  (Laravel 11 style – replace the entire file)
// Registers the DemoAuth middleware alias so routes/web.php can use it.
// ──────────────────────────────────────────────────────────────────────────────

use App\Http\Middleware\DemoAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies (ngrok, load balancers) so https:// URLs are generated correctly
        $middleware->trustProxies(at: '*');

        // Inertia share middleware
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Register our custom demo-auth alias
        $middleware->alias([
            'demo.auth' => DemoAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
