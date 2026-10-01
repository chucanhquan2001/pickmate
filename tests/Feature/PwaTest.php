<?php

it('exposes an installable web app manifest', function () {
    $response = $this->get('/manifest.webmanifest');

    $response->assertOk()
        ->assertHeader('content-type', 'application/manifest+json');

    expect($response->headers->get('cache-control'))->toContain('no-cache');

    expect($response->json())
        ->name->toBe('Pickmate')
        ->short_name->toBe('Pickmate')
        ->display->toBe('standalone')
        ->start_url->toBe('/')
        ->scope->toBe('/')
        ->lang->toBe('vi')
        ->icons->toHaveCount(3);

    expect(is_file(public_path('icons/apple-touch-icon.png')))->toBeTrue()
        ->and(is_file(public_path('icons/icon-192.png')))->toBeTrue()
        ->and(is_file(public_path('icons/icon-512.png')))->toBeTrue()
        ->and(is_file(public_path('icons/icon-512-maskable.png')))->toBeTrue()
        ->and(is_file(public_path('offline.html')))->toBeTrue();
});

it('serves the service worker as javascript', function () {
    $response = $this->get('/sw.js');

    $response->assertOk()
        ->assertHeader('content-type', 'application/javascript; charset=UTF-8')
        ->assertSee('pickmate-v1', false)
        ->assertSee('/auth/', false)
        ->assertSee('/offline.html', false);

    expect($response->headers->get('cache-control'))->toContain('no-cache');

    $nginx = file_get_contents(base_path('docker/nginx/default.conf'));

    expect($nginx)->toContain('location = /sw.js')
        ->and($nginx)->toContain('location = /manifest.webmanifest')
        ->and($nginx)->toContain('Cache-Control "no-cache"');
});

it('advertises the home screen app on the login page', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('rel="manifest" href="/manifest.webmanifest"', false)
        ->assertSee('name="theme-color" content="#0f766e"', false)
        ->assertSee('name="apple-mobile-web-app-capable" content="yes"', false)
        ->assertSee('name="apple-mobile-web-app-status-bar-style" content="black-translucent"', false)
        ->assertSee('name="apple-mobile-web-app-title" content="Pickmate"', false)
        ->assertSee('rel="apple-touch-icon" href="/icons/apple-touch-icon.png"', false);

    expect(file_get_contents(resource_path('views/app.blade.php')))
        ->toContain("navigator.serviceWorker.register('/sw.js')");
});
