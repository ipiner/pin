<?php

declare(strict_types=1);

use App\Services\UserService;
use Illuminate\Support\Str;
use Pin\Errors\Errors;
use Pin\Services\Results\UpdateResult;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\User;

uses(InteractsWithDatabase::class);

it('updates a user successfully', function () {
    $service = new UserService();
    $user = $service->create(['username' => Str::random()])->model;

    $username = Str::random();
    $result = $service->update($user, ['username' => $username]);

    expect($result)->toBeInstanceOf(UpdateResult::class)
        ->and($result->model->username)->toBe($username)
        ->and($result->updated)->toBeTrue()
        ->and($result->model)->toBeInstanceOf(User::class);
});

it('supports update callback', function () {
    $service = new UserService();
    $user = $service->create(['username' => Str::random()])->model;

    $service->update(
        $user,
        ['username' => Str::random()],
        fn (User $user) => $user->username = 'foo'
    );

    expect($user->username)->toBe('foo');
});

it('handles updating version checks', function () {
    $service = new class extends UserService
    {
        public function updating($model, array &$data): void
        {
            parent::updating($model, $data);
        }
    };

    $data = [];
    $service->updating(new User(), $data);
    expect($data)->toBe([]);

    $data = ['v' => 1];
    $service->updating(new User(['v' => 1]), $data);
    expect($data)->toBe(['v' => 2]);

    $this->expectExceptionCode(Errors::DataVersionMismatch->code());
    $service->updating(new User(['v' => 20]), $data);
});
