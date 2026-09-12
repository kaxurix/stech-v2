<?php
$tests = [
    ['/js/filament/actions/actions.js?v=5', 'actions.js', 'filamentActionModals'],
    ['/js/filament/filament/app.js?v=5', 'filament/app.js', null],
    ['/js/filament/filament/echo.js?v=5', 'filament/echo.js', null],
    ['/css/filament/filament/app.css?v=5', 'filament/app.css CSS', '--fi-'],
    ['/js/filament/forms/components/file-upload.js?v=5', 'forms/file-upload.js', 'FilePond'],
    ['/vendor/livewire/livewire.min.js?id=x', 'livewire.min.js', null],
];

foreach ($tests as [$qs, $label, $needle]) {
    $_SERVER['QUERY_STRING'] = $qs;
    ob_start();
    include __DIR__ . '/../public/filament-asset.php';
    $out = ob_get_clean();

    $bytes = strlen($out);
    if ($bytes < 100) {
        $status = "FAIL [{$bytes}] " . substr($out, 0, 100);
    } elseif ($needle && !str_contains($out, $needle)) {
        $status = "FAIL - expected '{$needle}' not found [{$bytes} bytes]";
    } else {
        $status = "OK [{$bytes} bytes]";
    }

    echo "{$label}: {$status}\n";
}
