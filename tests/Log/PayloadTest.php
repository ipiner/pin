<?php

declare(strict_types=1);

use Pin\Log\Payload;

it('has empty context by default and can set context values', function () {
    $payload = new Payload();

    expect(isset($payload->context))->toBeFalse();

    $payload->context(['a' => 'a']);
    expect($payload->context)->toBe(['a' => 'a']);

    $payload->context(['a' => 1, 'b' => 'b']);
    expect($payload->context)->toBe(['a' => 1, 'b' => 'b']);
});
