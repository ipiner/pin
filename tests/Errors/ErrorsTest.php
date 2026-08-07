<?php

declare(strict_types=1);

use Pin\Errors\Errors;

it('resolves errors', function () {
    expect(Errors::ModelNotFound->statusCode())->toBe(404)
        ->and(Errors::get(Errors::Success->code())->message())->toBe('Success')
        ->and(Errors::getMessage(Errors::Success->code()))->toBe('Success');
});
