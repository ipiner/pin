<?php

declare(strict_types=1);

use Pin\IdGenerator\RedisId;
use Pin\Testing\Concerns\InteractsWithRedis;

uses(InteractsWithRedis::class);

it('generates redis ids', function () {
    expect(app('pin.id.redis')->generate())->toBe(1)
        ->and(app('pin.id.redis')->generate())->toBe(2);

    // lock
    $generator = new RedisId([
        'name' => 'default',
        'use_lock' => true,
    ]);

    expect($generator->generate())->toBe(3);
});

it('generates multiple redis ids', function () {
    expect(app('pin.id.redis')->generate(5))
        ->toBe([1, 2, 3, 4, 5])

        ->and(app('pin.id.redis')->generate(3))
        ->toBe([6, 7, 8]);

    $generator = new RedisId([
        'name' => 'testing',
        'use_lock' => false,
    ]);

    expect($generator->generate(5))
        ->toBe([1, 2, 3, 4, 5])

        ->and($generator->generate(3))
        ->toBe([6, 7, 8]);
});
