<?php

declare(strict_types=1);

use Pin\Models\Model;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\User;

uses(InteractsWithDatabase::class);

it('gets per page value', function () {
    $model = new Model();
    expect($model->getPerPage())->toBe(15);

    $this->invoker($model)->perPage = 20;
    expect($model->getPerPage())->toBe(20);
});

it('serializes date', function () {
    expect(
        $this->invoker(Model::class)->serializeDate(new DateTime('2022-01-01 09:00:00'))
    )->toBe('2022-01-01 09:00:00');
});

it('runs transaction', function () {
    expect(
        (new User())->transaction(function () {
            User::create([
                'id' => 2,
                'username' => 'foo',
                'realname' => 'foo',
            ]);

            return true;
        })
    )->toBeTrue();
});

it('keeps table names independent between instances', function () {
    $first = new Model()->setTable('custom_models');
    $second = new Model();

    expect($first->getTable())->toBe('custom_models')
        ->and($second->getTable())->toBe('models')
        ->and($first->setTable('other_models')->getTable())->toBe('other_models')
        ->and($second->getTable())->toBe('models');
});

it('rolls back failed transactions without closing the outer transaction', function () {
    $model = new User();
    $connection = $model->getConnection();
    $level = $connection->transactionLevel();
    $connection->beginTransaction();

    try {
        expect(fn () => $model->transaction(function (User $model) {
            $model->create(['id' => 123, 'username' => 'rollback', 'realname' => 'rollback']);

            throw new RuntimeException('rollback');
        }))->toThrow(RuntimeException::class, 'rollback');

        expect(User::query()->whereKey(123)->exists())->toBeFalse()
            ->and($connection->transactionLevel())->toBe($level + 1);
    } finally {
        $connection->rollBack($level);
    }
});
