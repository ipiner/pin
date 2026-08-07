<?php

declare(strict_types=1);

use Pin\Services\Results\UpdateResult;
use Pin\Tests\Models\Models\User;

it('returns correct update result when updated is true', function () {
    $result = new UpdateResult(new User(['v' => 1]), true);

    expect(json_encode($result))->toBe('{"updated":true,"v":1}')
        ->and($result->message())->toBe('Update successfully');
});

it('returns correct update result when updated is false', function () {
    $result = new UpdateResult(new User(), false);

    expect(json_encode($result))->toBe('{"updated":false}')
        ->and($result->message())->toBe('Update failed');
});
