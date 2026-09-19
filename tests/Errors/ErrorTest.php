<?php

declare(strict_types=1);

use Pin\Errors\Error;
use Pin\Errors\Errors;
use Pin\Tests\Errors\Fixtures\ExtendedError;

it('parses error definitions', function (
    string $definition,
    int $code,
    string $message,
    int $statusCode
): void {
    $invoker = $this->invoker(Error::class);
    /** @var Error $err */
    $err = $invoker->parseInternal($definition);

    expect($err->code)->toBe($code)
        ->and($err->messageKey)->toBe($message)
        ->and($err->statusCode)->toBe($statusCode);
})->with([
    'status and message' => [
        '201|created',
        201,
        'created',
        201,
    ],

    'status message and custom http status' => [
        '201|200|created',
        201,
        'created',
        200,
    ],
    'business error' => ['10000|failed', 10000, 'failed', 200],
    'message containing a separator' => ['10000|422|left|right', 10000, 'left|right', 422],
]);

it('keeps parsed errors separate for subclasses', function () {
    $error = Error::parse(Errors::Success);
    $extended = ExtendedError::parse(Errors::Success);

    expect($extended)->toBeInstanceOf(ExtendedError::class)
        ->and($extended)->not->toBe($error)
        ->and(Error::parse(Errors::Success))->toBe($error)
        ->and(ExtendedError::parse(Errors::Success))->toBe($extended);
});
