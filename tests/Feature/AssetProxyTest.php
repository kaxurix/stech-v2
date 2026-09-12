<?php

namespace Tests\Feature;

use Tests\TestCase;

class AssetProxyTest extends TestCase
{
    /**
     * Livewire JS route registered correctly at /livewire-script.
     * The route exists and doesn't redirect or 404.
     * (Content check not possible in test — route uses readfile() + exit)
     */
    public function test_livewire_script_route_is_registered(): void
    {
        // Route should be registered (not 404) — status will be 200 on real server
        // In test environment, Livewire uses pretendResponseIsFile which exits early.
        // We just verify the route resolves without a "Route not found" exception.
        $routes = app('router')->getRoutes();
        $found = false;
        foreach ($routes->getRoutes() as $route) {
            if ($route->uri() === 'livewire-script') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Route /livewire-script should be registered');
    }

    /**
     * filament-asset.php serves JS from vendor/ (tested via direct PHP include).
     */
    public function test_filament_asset_php_serves_js(): void
    {
        $tests = [
            '/js/filament/actions/actions.js?v=5.7.1.0'             => 'filamentActionModals',
            '/js/filament/support/support.js?v=5.7.1.0'             => null,
            '/js/filament/filament/app.js?v=5.7.1.0'                => null,
            '/js/filament/filament/echo.js?v=5.7.1.0'               => null,
            '/js/filament/forms/components/file-upload.js?v=5.7.1.0' => 'FilePond',
        ];

        foreach ($tests as $qs => $needle) {
            $_SERVER['QUERY_STRING'] = $qs;
            ob_start();
            // We must trap the exit() call by wrapping in try/catch won't work,
            // so we use output buffering and check what was printed BEFORE exit.
            try {
                @include public_path('filament-asset.php');
            } catch (\Throwable $e) {
                // exit() throws nothing in PHP — output is already buffered
            }
            $out = ob_get_clean();

            $this->assertGreaterThan(100, strlen($out), "Empty output for: $qs");
            if ($needle) {
                $this->assertStringContainsString($needle, $out, "Missing '$needle' for: $qs");
            }
        }
    }

    /**
     * filament-asset.php serves CSS (not JS) for CSS paths.
     */
    public function test_filament_asset_php_serves_css(): void
    {
        $_SERVER['QUERY_STRING'] = '/css/filament/filament/app.css?v=5.7.1.0';
        ob_start();
        @include public_path('filament-asset.php');
        $out = ob_get_clean();

        $this->assertGreaterThan(100, strlen($out));
        $this->assertStringNotContainsString('filamentActionModals', $out, 'CSS must not contain JS code');
    }
}
