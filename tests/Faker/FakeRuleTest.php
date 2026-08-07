<?php

declare(strict_types=1);

use Pin\Faker\FakeRule;

it('handles fake rule parameters', function () {
    $rule = new FakeRule('in', [0, 1]);

    // validate
    $rule->validate('s', 's', fn () => true);

    expect($rule->parameters())->toBe([0, 1])
        ->and($rule->parameter(0))->toBe(0)
        ->and($rule->parameter(1))->toBe(1)
        ->and($rule->parameter(2, 's'))->toBe('s');
});
