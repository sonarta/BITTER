<?php

test('app includes PWA manifest and theme color meta', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('rel="manifest" href="/build/manifest.webmanifest"', false)
        ->assertSee('name="theme-color" content="#1e63a8"', false)
        ->assertSee('rel="apple-touch-icon" href="/biru@4x.png"', false);
});
