<?php

declare(strict_types=1);

use Pin\Faker\RuleBag;

it('handles RuleBag rules and parameters', function () {
    $rules = new RuleBag([
        'nullable',
        'string',
        'integer',
        'required',
        'min:16',
        'max:32',
        'in: 0,  1 ',
        new stdClass(),
    ]);

    expect($rules)
        ->has('string')->toBeTrue()
        ->and($rules->has('integer'))->toBeTrue()
        ->and($rules->isNullable())->toBeTrue()
        ->and($rules->isRequired())->toBeTrue()

        ->and($rules->parameter('string'))->toBeNull()
        ->and($rules->parameter('min'))->toBe('16')
        ->and($rules->parameter('max'))->toBe('32')
        ->and($rules->parameters('in'))->toBe(['0', '1']);
});
