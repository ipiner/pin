<?php

declare(strict_types=1);

use Pin\Crypt\CryptServiceProvider;

it('declares provided services', function () {
    $provider = new CryptServiceProvider($this->app);
    expect($provider->provides())->toBe([
        'pin.crypt.aes',
        'pin.crypt.rsa',
    ]);
});
