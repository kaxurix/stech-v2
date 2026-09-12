@echo off
REM Multi-worker dev server. Filament/Livewire loads many assets in parallel and the
REM default single-worker `artisan serve` starves them, causing 30s timeouts on login.
set "PATH=C:\php84;%PATH%"
set "PHP_CLI_SERVER_WORKERS=8"
cd /d "%~dp0.."
"C:\php84\php.exe" artisan serve --no-reload
