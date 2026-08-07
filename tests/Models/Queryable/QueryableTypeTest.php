<?php

declare(strict_types=1);

use Pin\Models\Queryable\QueryableType;
use Pin\Validation\Rules\Queryable;

describe('parse', function () {
    it('parses enum instance', function () {
        [$type, $columns] = QueryableType::parse(QueryableType::Like);

        expect($type)->toBe(QueryableType::Like)
            ->and($columns)->toBeNull();
    });

    it('parses simple string', function () {
        [$type, $columns] = QueryableType::parse('eq');

        expect($type)->toBe(QueryableType::Eq)
            ->and($columns)->toBeNull();
    });

    it('parses colon separated columns', function () {
        [$type, $columns] = QueryableType::parse('ns:id,name');

        expect($type)->toBe(QueryableType::Ns)
            ->and($columns)->toBe(['id', 'name']);
    });

    it('parses pipe separated columns', function () {
        [$type, $columns] = QueryableType::parse('ns:id|name');

        expect($type)->toBe(QueryableType::Ns)
            ->and($columns)->toBe(['id', 'name']);
    });

    it('parses comma separated columns', function () {
        [$type, $columns] = QueryableType::parse('ns,id,name');

        expect($type)->toBe(QueryableType::Ns)
            ->and($columns)->toBe(['id', 'name']);
    });

    it('normalizes all supported separators', function () {
        [$type, $columns] = QueryableType::parse('ns:id,name|email');

        expect($type)->toBe(QueryableType::Ns)
            ->and($columns)->toBe(['id', 'name', 'email']);
    });

    it('throws for invalid type', function () {
        QueryableType::parse('unknown');
    })->throws(ValueError::class);
});

describe('asRule', function () {
    it('creates rule without columns', function () {
        $rule = QueryableType::Like->asRule();

        expect($rule)->toBeInstanceOf(Queryable::class)
            ->and((string) $rule)->toBe('q:like');
    });

    it('creates rule with columns', function () {
        $rule = QueryableType::Ns->asRule('id|name');

        expect((string) $rule)
            ->toBe('q:ns,id,name');
    });
});

it('returns comparison symbol', function (QueryableType $type, ?string $expected) {
    expect($type->comparisonSymbol())
        ->toBe($expected);
})->with([
    [QueryableType::Gt, '>'],
    [QueryableType::GtNumeric, '>'],
    [QueryableType::Gte, '>='],
    [QueryableType::GteNumeric, '>='],
    [QueryableType::Lt, '<'],
    [QueryableType::LtNumeric, '<'],
    [QueryableType::Lte, '<='],
    [QueryableType::LteNumeric, '<='],
    [QueryableType::Eq, null],
    [QueryableType::Like, null],
]);

it('detects in type', function () {
    expect(QueryableType::In->isIn())->toBeTrue()
        ->and(QueryableType::InNumeric->isIn())->toBeTrue()
        ->and(QueryableType::Like->isIn())->toBeFalse();
});

describe('isLike', function () {
    it('detects like type', function () {
        expect(QueryableType::Like->isLike())->toBeTrue()
            ->and(QueryableType::StartsWith->isLike())->toBeTrue()
            ->and(QueryableType::EndsWith->isLike())->toBeTrue()
            ->and(QueryableType::Eq->isLike())->toBeFalse();
    });
});

it('detects numeric type', function () {
    expect(QueryableType::EqNumeric->isNumeric())->toBeTrue()
        ->and(QueryableType::GtNumeric->isNumeric())->toBeTrue()
        ->and(QueryableType::RangeNumeric->isNumeric())->toBeTrue()
        ->and(QueryableType::Eq->isNumeric())->toBeFalse()
        ->and(QueryableType::Like->isNumeric())->toBeFalse();
});

it('detects range type', function () {
    expect(QueryableType::Range->isRange())->toBeTrue()
        ->and(QueryableType::RangeNumeric->isRange())->toBeTrue()
        ->and(QueryableType::Eq->isRange())->toBeFalse();
});
