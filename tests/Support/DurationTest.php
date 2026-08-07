<?php

declare(strict_types=1);

use Pin\Support\Duration;
use Pin\Support\Size;

it('calculates seconds', function () {
    expect((new Duration(1, 3))->seconds())->toBe(2.0)
        ->and((new Duration(1, 3.2))->seconds())->toBe(2.2)
        ->and((new Duration(1, 3.20258))->seconds())->toBe(2.2026)
        ->and((new Duration(1, 3.20258))->seconds(2))->toBe(2.2);
});

it('calculates milliseconds', function () {
    expect((new Duration(1, 3))->milliseconds())->toBe(2000)
        ->and((new Duration(1, 3.2))->milliseconds())->toBe(2200)
        ->and((new Duration(1, 3.20258))->milliseconds())->toBe(2202);
});

it('calculates memory usage', function () {
    $duration = new Duration(1, 2);

    $a = str_repeat('*', Size::M);

    expect(strlen($a))->toBe(Size::M)
        ->and($duration->memoryUsage() > Size::M)->toBeTrue();
});
