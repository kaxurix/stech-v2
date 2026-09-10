<?php

namespace Tests\Feature;

use Tests\TestCase;

class AssetProxyTest extends TestCase
{
    public function test_filament_js_asset_proxy(): void
    {
        $response = $this->get('/js/filament/filament/app.js');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    }

    public function test_filament_forms_component_asset_proxy(): void
    {
        $response = $this->get('/js/filament/forms/components/file-upload.js');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    }

    public function test_livewire_asset_proxy(): void
    {
        $response = $this->get('/vendor/livewire/livewire.min.js');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    }
}
