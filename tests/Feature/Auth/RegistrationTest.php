<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '1234',
        'password_confirmation' => '1234',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users are automatically logged in after registering', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Auto Login User',
        'email' => 'autologin@example.com',
        'password' => '1234',
        'password_confirmation' => '1234',
    ]);

    $this->assertAuthenticated();
    $this->get(route('dashboard'))->assertOk();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users can access the dashboard immediately when email verification is disabled', function () {
    $fortifyFeatures = config('fortify.features', []);

    config([
        'fortify.email_verification_enabled' => false,
        'fortify.features' => array_values(array_filter(
            is_array($fortifyFeatures) ? $fortifyFeatures : [],
            fn (string|array $feature): bool => $feature !== Features::emailVerification(),
        )),
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'password' => '1234',
        'password_confirmation' => '1234',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->get(route('dashboard'))->assertOk();
});
