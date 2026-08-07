<?php

declare(strict_types=1);

use App\Services\UserService;
use Illuminate\Support\Str;
use Pin\Services\Results\DeleteResult;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\User;

uses(InteractsWithDatabase::class);

it('deletes a user successfully', function () {
    $service = new UserService();
    $user = $service->create(['username' => Str::random()])->model;

    $result = $service->delete($user);

    expect($result)->toBeInstanceOf(DeleteResult::class)
        ->and($result->deleted)->toBeTrue()
        ->and($result->model)->toBeInstanceOf(User::class);
});

it('supports delete callback', function () {
    $service = new UserService();
    $username = Str::random();

    $user = $service->create(['username' => $username])->model;

    $service->delete(
        $user,
        fn (User $user) => $user->username = 'foo'
    );

    expect($user->username)->toBe('foo');
});
