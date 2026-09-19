<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Pin\Password\Password;
use Pin\Password\PasswordServiceProvider;

it('declares provided services', function () {
    $provider = new PasswordServiceProvider($this->app);
    expect($provider->provides())->toBe(['pin.password']);
});

it('resolves the deferred service before the application boots', function () {
    $app = new Application();
    $app->addDeferredServices(['pin.password' => PasswordServiceProvider::class]);

    try {
        expect($app->make('pin.password'))->toBeInstanceOf(Password::class)
            ->and($app->make('pin.password'))->toBe($app->make('pin.password'));
    } finally {
        Application::setInstance($this->app);
    }
});
