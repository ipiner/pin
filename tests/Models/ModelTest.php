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
