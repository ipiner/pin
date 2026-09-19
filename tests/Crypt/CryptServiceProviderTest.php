<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Pin\Crypt\Aes;
use Pin\Crypt\CryptServiceProvider;
use Pin\Crypt\Rsa;

it('declares provided services', function () {
    $provider = new CryptServiceProvider($this->app);
    expect($provider->provides())->toBe([
        'pin.crypt.aes',
        'pin.crypt.rsa',
    ]);
});

it('resolves deferred services before the application boots', function () {
    $app = new Application();
    $app->addDeferredServices([
        'pin.crypt.aes' => CryptServiceProvider::class,
        'pin.crypt.rsa' => CryptServiceProvider::class,
    ]);

    try {
        expect($app->make('pin.crypt.aes'))->toBeInstanceOf(Aes::class)
            ->and($app->make('pin.crypt.rsa'))->toBeInstanceOf(Rsa::class)
            ->and($app->make('pin.crypt.aes'))->toBe($app->make('pin.crypt.aes'))
            ->and($app->make('pin.crypt.rsa'))->toBe($app->make('pin.crypt.rsa'));
    } finally {
        Application::setInstance($this->app);
    }
});
