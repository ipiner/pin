<?php

declare(strict_types=1);

use Pin\Models\Queryable\Queryable;
use Pin\Models\Queryable\QueryableType;

it('creates queryable rules from validation rules', function () {
    $rules = [
        'q' => 'nullable|'.QueryableType::Ns->asRule('id|username|realname'),
        'name' => 'nullable|q:like',
        'age' => ['nullable', QueryableType::GtNumeric->asRule()],
        'null' => 'nullable',
    ];

    $conditions = Queryable::fromRules($rules)->conditions;

    expect($conditions['q']->type)->toBe('ns,id,username,realname')
        ->and($conditions['name']->type)->toBe(QueryableType::Like->value)
        ->and($conditions['age']->type)->toBe(QueryableType::GtNumeric->value)
        ->and(isset($conditions['null']))->toBeFalse();
});
