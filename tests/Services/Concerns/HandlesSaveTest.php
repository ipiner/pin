<?php

declare(strict_types=1);

use App\Services\UserService;

it('checks handles save null-to-empty-string conversion', function () {
    $service = new UserService();
    $invoker = $this->invoker($service);

    expect($invoker->shouldConvertNullToEmptyString())->toBeTrue();

    $service->context('convertNullToEmptyString', false);
    expect($invoker->shouldConvertNullToEmptyString())->toBeFalse();
});
