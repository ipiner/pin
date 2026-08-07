<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Errors\Registry;

it('returns unknown error when code does not exist', function () {
    expect(Registry::get(time())->code())->toBe(Errors::Unknown->code());
});

it('loads registry paths', function () {
    expect(Registry::load('xxxx'))->toBeFalse()
        ->and(Registry::load(__DIR__, 'Pin\\Tests\\Errors'))->toBeTrue();

    expect(Errors::get(-1))->toBe(Pin\Tests\Errors\Errors::DoesNothing)
        ->and(Pin\Tests\Errors\Errors::DoesNothing->message())->toBe('does nothing');
});
