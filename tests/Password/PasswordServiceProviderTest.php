<?php

declare(strict_types=1);

use Pin\Password\PasswordServiceProvider;

it('declares provided services', function () {
    $provider = new PasswordServiceProvider($this->app);
    expect($provider->provides())->toBe(['pin.password']);
});
