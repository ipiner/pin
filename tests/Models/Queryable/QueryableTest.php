<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Request;
use Pin\Models\Queryable\Queryable;
use Pin\Models\Queryable\QueryableCondition;
use Pin\Models\Queryable\QueryableType;
use Pin\Tests\Models\Models\User;

it('builds queryable sql', function () {
    $cases = [
        ['!"q" = ', null],
        ['!"q" = ', ['q' => 'eq']],
        ['!"q" = ', new QueryableCondition('q', '', 'eq')],
        ['"realname" = \'s\'', Queryable::fromPayload(['realname' => 's'], ['realname' => 'eq'])],

        ['"q" in (1, 2)', 'q', ['1', '2'], QueryableType::InNumeric],

        ['"q" = \'1\'', 'q', '1', QueryableType::Eq],
        ['"q" = 1', 'q', '1', QueryableType::EqNumeric],

        ['"q" like \'%s%\'', 'q', 's', QueryableType::Like],
        ['"a" like \'%s%\' or "b" like \'%s%\'', 'a|b', 's', QueryableType::Like],
        ['"q" like \'s%\'', 'q', 's', QueryableType::StartsWith],
        ['"q" like \'%s\'', 'q', 's', QueryableType::EndsWith],

        ['"q" > \'1\'', 'q', '1', QueryableType::Gt],
        ['"q" > 1', 'q', '1', QueryableType::GtNumeric],

        ['"q" >= \'1\'', 'q', '1', QueryableType::Gte],
        ['"q" >= 1', 'q', '1', QueryableType::GteNumeric],

        ['"q" < \'1\'', 'q', '1', QueryableType::Lt],
        ['"q" < 1.1', 'q', '1.10', QueryableType::LtNumeric],

        ['"q" <= \'1\'', 'q', '1', QueryableType::Lte],
        ['"q" <= 1', 'q', '1', QueryableType::LteNumeric],

        ['"q" in (\'1\', \'2\')', 'q', '1,2', QueryableType::In],
        ['"q" in (1, 2)', 'q', '1,2.0', QueryableType::InNumeric],

        ['"q" >= \'1\'', 'q', '1,', QueryableType::Range],
        ['"q" >= \'1\' and "q" <= \'2\'', 'q', '1,2', QueryableType::Range],

        ['"q" >= 1', 'q', '1.0,', QueryableType::RangeNumeric],
        ['"q" >= 1 and "q" <= 2', 'q', '1,2', QueryableType::RangeNumeric],

        ['"uid" = 1', 'q', '1', 'ns:uid|username|realname'],
        ['"username" like \'%s%\' or "realname" like \'%s%\'', 'q', 's', 'ns:uid,username|realname'],
    ];

    foreach ($cases as $case) {
        $queryable = is_string($case[1])
            ? new Queryable([$case[1] => $case[2]], [$case[1] => $case[3]])
            : $case[1];

        $sql = User::queryable($queryable)->toRawSql();

        if (str_starts_with($case[0], '!')) {
            expect($sql)->not->toContain($case[0]);
        } else {
            expect($sql)->toContain($case[0]);
        }
    }
});

it('creates queryable from request', function () {
    expect(
        Queryable::fromRequest(['q' => '-'])->conditions['q']->value
    )->toBeNull();

    app()->request->query->set('q', '1');

    expect(
        Queryable::fromRequest(['q' => '-'])->conditions['q']->value
    )->toBe('1');

    $request = Request::create('/', 'GET', ['q' => 'foo']);

    expect(
        Queryable::fromRequest(['q' => '-'], $request)->conditions['q']->value
    )->toBe('foo');
});
