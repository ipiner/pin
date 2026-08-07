<?php

declare(strict_types=1);

use Pin\Tests\Models\Models\User;

it('sorts users', function () {
    expect(User::sort(null, [])->toSql())->toBe(
        'select * from "users" where "users"."deleted_at" = ?'
    );

    expect(User::sort('id', [])->toSql())->toBe(
        'select * from "users" where "users"."deleted_at" = ?'
    );

    expect(User::sort('id', ['id'])->toSql())->toBe(
        'select * from "users" where "users"."deleted_at" = ? order by "id" asc'
    );

    expect(User::sort(['-id'], 'id,name')->toSql())->toBe(
        'select * from "users" where "users"."deleted_at" = ? order by "id" desc'
    );

    expect(User::sort('-id,name', 'id,name')->toSql())->toBe(
        'select * from "users" where "users"."deleted_at" = ? order by "id" desc, "name" asc'
    );
});
