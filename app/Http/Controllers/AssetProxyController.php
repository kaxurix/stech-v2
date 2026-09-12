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
        $path = str_replace('\\', '/', str_replace('..', '', $path));
        $parts = explode('/', $path);
        if (count($parts) < 2) {
            abort(404);
        }

        $package = array_shift($parts); // e.g. 'filament', 'forms', 'tables', 'support', 'actions', 'notifications', 'schemas'
        $filename = implode('/', $parts); // e.g. 'app.js', 'actions.js', 'components/file-upload.js', 'app.css'

        $isCss = ($type === 'css') || str_contains($path, '.css');

        if ($isCss) {
            $possibleSubpaths = [
                $filename,
                'theme.css',
                'index.css',
                'app.css',
            ];
        } else {
            $possibleSubpaths = [
                $filename,
                'index.js',
                'app.js',
            ];
        }

        $baseDir = base_path("vendor/filament/{$package}/dist");

        foreach ($possibleSubpaths as $subpath) {
            $fullPath = $baseDir . '/' . $subpath;
            if (File::exists($fullPath) && !File::isDirectory($fullPath)) {
                if ($isCss && str_ends_with($fullPath, '.css')) {
                    return Response::file($fullPath, [
                        'Content-Type'  => 'text/css; charset=utf-8',
                        'Cache-Control' => 'public, max-age=31536000',
                    ]);
                }
                if (!$isCss && (str_ends_with($fullPath, '.js') || str_contains($fullPath, 'woff'))) {
                    $mimeType = str_contains($fullPath, 'woff') ? 'font/woff2' : 'application/javascript; charset=utf-8';
                    return Response::file($fullPath, [
                        'Content-Type'  => $mimeType,
                        'Cache-Control' => 'public, max-age=31536000',
                    ]);
                }
            }
        }

        abort(404);
    }

    /**
     * Serve Livewire JS assets dynamically from vendor/ directory when not present in public/
     */
    public function livewireAsset(string $path)
    {
        $path = str_replace('\\', '/', str_replace('..', '', $path));
        $fullPath = base_path("vendor/livewire/livewire/dist/{$path}");

        if (File::exists($fullPath) && !File::isDirectory($fullPath)) {
            $mimeType = str_ends_with($fullPath, '.css')
                ? 'text/css; charset=utf-8'
                : 'application/javascript; charset=utf-8';

            return Response::file($fullPath, [
                'Content-Type'  => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }

        abort(404);
    }
}
