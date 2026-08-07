<?php

declare(strict_types=1);

use Pin\Pagination\Pagination;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\User;

uses(InteractsWithDatabase::class);

it('returns pagination instance', function () {
    expect(User::pagination())->toBeInstanceOf(Pagination::class);
});
