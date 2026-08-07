<?php

declare(strict_types=1);

use Pin\Tests\Models\Models\User;

it('adds aggregate selects', function () {
    expect(
        User::addSelectCount()->addSelectCount('id', 'count_id')->toSql()
    )->toBe(
        'select count(*) as total, count(id) as count_id from "users" where "users"."deleted_at" = ?'
    );

    expect(
        User::addSelectSum('price')->addSelectSum('amount', 'amount')->toSql()
    )->toBe(
        'select sum(price) as sum_price, sum(amount) as amount from "users" where "users"."deleted_at" = ?'
    );

    expect(
        User::addSelectMax('age')->addSelectMin('age')->addSelectAvg('age')->toSql()
    )->toBe(
        'select max(age) as max_age, min(age) as min_age, avg(age) as avg_age from "users" where "users"."deleted_at" = ?'
    );
});
