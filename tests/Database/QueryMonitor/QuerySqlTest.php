<?php

declare(strict_types=1);

use Pin\Database\QueryMonitor\QuerySql;
use Pin\Tests\Database\QueryMonitor\TestCase;

uses(TestCase::class);

it('generates raw SQL', function () {
    config(['pin.logging.sql_max_length' => 10240]);

    expect(QuerySql::raw($this->getQueryExecuted()))
        ->toBe('select username from users where uid = 1');
});

it('does not truncate SQL when within max length', function () {
    config(['pin.logging.sql_max_length' => 100]);

    $sql = 'select * from users';
    expect(QuerySql::truncate($sql))->toBe($sql);
});

it('truncates SQL when exceeding max length', function () {
    config(['pin.logging.sql_max_length' => 10]);

    expect(QuerySql::truncate('select * from users'))
        ->toBe('select * f(...9)');
});
