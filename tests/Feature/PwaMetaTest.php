<?php

test('app includes PWA manifest and theme color meta', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('rel="manifest"', false)
        ->assertSee('manifest.webmanifest', false)
        ->assertSee('name="theme-color"', false);
});
