<?php

declare(strict_types=1);

use Pin\Services\Results\DeleteResult;
use Pin\Tests\Models\Models\User;

it('returns correct delete result when deleted is true', function () {
    $result = new DeleteResult(new User(), true);

    expect(json_encode($result))->toBe('{"deleted":true}')
        ->and($result->message())->toBe('Delete successfully');
});

it('returns correct delete result when deleted is false', function () {
    $result = new DeleteResult(new User(), false);

    expect(json_encode($result))->toBe('{"deleted":false}')
        ->and($result->message())->toBe('Delete failed');
});
