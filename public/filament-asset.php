<?php

/**
 * Standalone Filament & Livewire Asset Proxy Loader
 * Serves assets dynamically to bypass Nginx static 404 rules and Maldet antivirus auto-deletion.
 * 
 * URL format: /filament-asset.php?/js/filament/actions/actions.js?v=5.7.1.0
 * QUERY_STRING will be: /js/filament/actions/actions.js?v=5.7.1.0
 */

// Get and clean URI from query string
$uri = $_SERVER['QUERY_STRING'] ?? '';

// Strip ALL query params (?v=xxx, ?id=xxx, etc.) from the end
// Note: the URI itself starts with "/" so the first "?" we want to strip is after the filename
if (($qPos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $qPos);
}

// Normalize slashes and remove leading slash
$uri = str_replace('\\', '/', ltrim($uri, '/'));

$fullPath = null;

if (str_starts_with($uri, 'vendor/livewire/')) {
    // Livewire assets: /vendor/livewire/livewire.min.js
    $file = substr($uri, strlen('vendor/livewire/'));
    $file = str_replace('..', '', $file);
    $fullPath = dirname(__DIR__) . '/vendor/livewire/livewire/dist/' . $file;

} elseif (str_starts_with($uri, 'js/filament/') || str_starts_with($uri, 'css/filament/') || str_starts_with($uri, 'fonts/filament/')) {
    // Filament assets: /js/filament/{package}/{filename}
    // e.g. /js/filament/actions/actions.js  → vendor/filament/actions/dist/index.js
    // e.g. /css/filament/filament/app.css   → vendor/filament/filament/dist/theme.css
    // e.g. /fonts/filament/filament/inter/index.css → vendor/filament/filament/dist/fonts/inter/index.css

    $type = explode('/', $uri)[0]; // 'js', 'css', or 'fonts'
    $withoutType = preg_replace('/^(js|css|fonts)\/filament\//', '', $uri);
    $parts = explode('/', $withoutType, 2);
    $package  = $parts[0];               // e.g. 'actions', 'filament', 'forms'
    $filename = $parts[1] ?? 'index.js'; // e.g. 'actions.js', 'app.js', 'components/file-upload.js'

    $isCss = ($type === 'css') || str_ends_with($filename, '.css');

    $baseDir = dirname(__DIR__) . '/vendor/filament/' . $package . '/dist';

    if ($isCss) {
        // CSS candidates — only pick CSS files
        $candidates = [
            $filename,
            'theme.css',
            'index.css',
        ];
    } else {
        // JS/font candidates — only pick JS or font files
        // Package-level files are named index.js in vendor but exposed as {package}.js
        $candidates = [
            $filename,           // exact match first (e.g. echo.js, components/file-upload.js)
            'index.js',          // fallback (e.g. actions.js → index.js)
        ];
    }

    foreach ($candidates as $candidate) {
        $try = $baseDir . '/' . $candidate;
        if (!file_exists($try) || is_dir($try)) {
            continue;
        }
        // Only accept .css files for CSS requests, .js for JS requests
        if ($isCss && str_ends_with($try, '.css')) {
            $fullPath = $try;
            break;
        }
        if (!$isCss && str_ends_with($try, '.js')) {
            $fullPath = $try;
            break;
        }
    }

} elseif (str_starts_with($uri, 'fonts/')) {
    // Font files served directly from public directory (they're not deleted by antivirus)
    $fontPath = __DIR__ . '/' . $uri;
    if (file_exists($fontPath) && !is_dir($fontPath)) {
        $fullPath = $fontPath;
    }
}

if ($fullPath !== null && file_exists($fullPath) && !is_dir($fullPath)) {
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    $mimeTypes = [
        'js'    => 'application/javascript; charset=utf-8',
        'css'   => 'text/css; charset=utf-8',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
    ];

    $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

    if (!headers_sent()) {
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=31536000');
        header('Access-Control-Allow-Origin: *');
    }
    readfile($fullPath);
    return; // Use return instead of exit so tests can include this file safely
}

// Asset not found
if (!headers_sent()) {
    http_response_code(404);
    header('Content-Type: text/plain');
}
echo 'Asset not found: ' . htmlspecialchars($uri);

