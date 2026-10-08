<?php

declare(strict_types=1);

use Pin\Services\Results\UpdateResult;
use Pin\Tests\Models\Models\User;

it('returns correct update result when updated is true', function () {
    $result = new UpdateResult(new User(), true);

    expect(json_encode($result))->toBe('{"updated":true}')
        ->and($result->message())->toBe('Update successfully');
});

it('returns correct update result when updated is false', function () {
    $result = new UpdateResult(new User(), false);

    expect(json_encode($result))->toBe('{"updated":false}')
        ->and($result->message())->toBe('Update failed');
});

it('preserves failed update flags', function () {
    $result = new UpdateResult(new User(), false);

    expect($result->toArray())->toBe(['updated' => false]);
});
