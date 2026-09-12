<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$response = $kernel->handle(Illuminate\Http\Request::create('/admin/login', 'GET'));
$html = $response->getContent();

// Extract script src URLs
preg_match_all('/<script[^>]+src="([^"]+)"[^>]*>/i', $html, $matches);
echo "SCRIPT URLs:\n";
foreach ($matches[1] as $url) {
    echo "  " . $url . "\n";
}

// Extract link hrefs
preg_match_all('/<link[^>]+href="([^"]+)"[^>]*>/i', $html, $linkMatches);
echo "\nLINK (CSS) URLs:\n";
foreach ($linkMatches[1] as $url) {
    echo "  " . $url . "\n";
}
