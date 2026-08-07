<?php

use Pin\Database\Schema\Metadata;
use Pin\Tests\Models\Models\User;

it('returns metadata instance', function () {
    $meta = User::metadata();

    expect(User::metadata())
        ->toBeInstanceOf(Metadata::class)
        ->and(User::metadata())->toBe($meta);
});
