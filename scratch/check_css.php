<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$response = $kernel->handle(Illuminate\Http\Request::create('/admin/login', 'GET'));
$html = $response->getContent();

preg_match_all('/<link[^>]+>/i', $html, $matches);
echo "LINK TAGS:\n";
print_r($matches[0]);
