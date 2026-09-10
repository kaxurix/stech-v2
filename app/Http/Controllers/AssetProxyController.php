<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class AssetProxyController extends Controller
{
    /**
     * Serve Filament JS/CSS assets dynamically from vendor/ directory when not present in public/
     */
    public function filamentAsset(string $type, string $path)
    {
        $parts = explode('/', $path);
        if (count($parts) < 2) {
            abort(404);
        }

        $package = array_shift($parts); // e.g. 'filament', 'forms', 'tables', 'support'
        $filename = implode('/', $parts); // e.g. 'app.js', 'components/file-upload.js', 'app.css'

        $possibleSubpaths = [$filename];

        if ($filename === 'app.js') {
            $possibleSubpaths[] = 'index.js';
        } elseif ($filename === 'app.css') {
            $possibleSubpaths[] = 'theme.css';
            $possibleSubpaths[] = 'index.css';
        }

        $baseDir = base_path("vendor/filament/{$package}/dist");

        foreach ($possibleSubpaths as $subpath) {
            $fullPath = $baseDir . '/' . $subpath;
            if (File::exists($fullPath)) {
                $mimeType = str_ends_with($fullPath, '.css')
                    ? 'text/css'
                    : 'application/javascript; charset=utf-8';

                return Response::file($fullPath, [
                    'Content-Type'  => $mimeType,
                    'Cache-Control' => 'public, max-age=31536000',
                ]);
            }
        }

        abort(404);
    }

    /**
     * Serve Livewire JS assets dynamically from vendor/ directory when not present in public/
     */
    public function livewireAsset(string $path)
    {
        // Sanitize path to prevent directory traversal
        $path = str_replace('..', '', $path);
        $fullPath = base_path("vendor/livewire/livewire/dist/{$path}");

        if (File::exists($fullPath)) {
            $mimeType = str_ends_with($fullPath, '.css')
                ? 'text/css'
                : 'application/javascript; charset=utf-8';

            return Response::file($fullPath, [
                'Content-Type'  => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }

        abort(404);
    }
}
