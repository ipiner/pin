<?php

declare(strict_types=1);

use Pin\Support\Size;

it('formats bytes into readable size strings', function () {
    expect(Size::format(2147483648))->toBe('2G')
        ->and(Size::format(536870912))->toBe('512M')
        ->and(Size::format(524288))->toBe('512K')
        ->and(Size::format(512))->toBe('512B');
});

it('converts size strings to bytes', function () {
    expect(Size::toBytes('10 b'))->toBe(10)
        ->and(Size::toBytes('10B'))->toBe(10)
        ->and(Size::toBytes('1k'))->toBe(1024)
        ->and(Size::toBytes('512k'))->toBe(524288)
        ->and(Size::toBytes('0.5 m'))->toBe(524288)
        ->and(Size::toBytes('0.5MB'))->toBe(524288)
        ->and(Size::toBytes('0.5MMMMMB'))->toBe(524288)
        ->and(Size::toBytes('1m'))->toBe(1048576)
        ->and(Size::toBytes('2g'))->toBe(2147483648);

    Size::toBytes('3bytes');
})->throws(InvalidArgumentException::class);
