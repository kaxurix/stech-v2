<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Route Filament JS/CSS through filament-asset.php (standalone PHP file).
        // This bypasses Nginx's rule that blocks .js/.css requests from reaching PHP,
        // and avoids antivirus (Maldet) auto-deletion of files in public/.
        config(['filament.assets_path' => 'filament-asset.php?']);

        // Override Livewire's JS URL to /livewire-script (no .js extension).
        // Nginx passes non-static-file requests to PHP, so this route always works.
        // The file is served directly from vendor/ — antivirus never touches it.
        $this->app->booted(function () {
            app(\Livewire\Mechanisms\FrontendAssets\FrontendAssets::class)
                ->setScriptRoute(function ($handle) {
                    return \Illuminate\Support\Facades\Route::get('livewire-script', $handle);
                });
        });
    }
}




