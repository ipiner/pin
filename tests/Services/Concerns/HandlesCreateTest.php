<?php

declare(strict_types=1);

use App\Services\UserService;
use Illuminate\Support\Str;
use Pin\Services\Results\CreateResult;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\User;

uses(InteractsWithDatabase::class);

it('creates a user successfully', function () {
    $service = new UserService();
    $username = Str::random();

    $result = $service->create(['username' => $username]);

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->model->username)->toBe($username)
        ->and($result->model->exists)->toBeTrue()
        ->and($result->model)->toBeInstanceOf(User::class);
});

it('supports create callback', function () {
    $service = new UserService();

    $result = $service->create(
        ['username' => Str::random()],
        fn (User $user) => $user->username = 'foo'
    );

    expect($result->model->username)->toBe('foo');
});
