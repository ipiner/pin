<?php

declare(strict_types=1);

use Pin\Errors\Error;

it('parses error definitions', function (
    string $definition,
    int $code,
    string $message,
    int $statusCode
): void {
    $invoker = $this->invoker(Error::class);
    /** @var Error $err */
    $err = $invoker->parseInternal($definition, $definition);

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
]);
