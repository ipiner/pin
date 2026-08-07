<?php

declare(strict_types=1);

use Pin\Models\Cache\KeyGenerator;
use Pin\Tests\Models\Models\User;

it('generates all cache keys', function () {
    expect(KeyGenerator::forAll('users'))->toBe('users-all')
        ->and(KeyGenerator::forAll(new User()))->toBe('users-all');
});

it('generates item cache keys', function () {
    expect(KeyGenerator::forItem('users', 1))->toBe('users:1')
        ->and(KeyGenerator::forItem(new User(), '1'))->toBe('users:1');
});
