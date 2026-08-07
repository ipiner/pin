<?php

declare(strict_types=1);

use Pin\Token\Drivers\AesDriver;
use Pin\Token\TokenFactory;

it('encodes, decodes and proxies driver methods', function () {
    $factory = new TokenFactory(new class extends AesDriver
    {
        public function foo(): string
        {
            return 'foo';
        }
    });

    expect($factory->driver())->toBeInstanceOf(AesDriver::class);

    $raw = $factory->encode(['uid' => 1]);

    expect($factory->decode($raw)->uid)->toBe(1)
        ->and($factory->foo())->toBe('foo');
});
