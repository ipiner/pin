<?php

declare(strict_types=1);

use Pin\Services\Results\CreateResult;
use Pin\Tests\Models\Models\User;

it('returns correct create result data and message', function () {
    $result = new CreateResult(new User(['id' => 1]));

    expect(json_encode($result))->toBe('{"id":1}')
        ->and($result->message())->toBe('Create successfully');
});
