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

it('returns failure when deleting an unsaved model', function () {
    $service = new UserService();
    $called = false;

    $result = $service->delete(new User(), function () use (&$called) {
        $called = true;
    });

    expect($result->deleted)->toBeFalse()
        ->and($called)->toBeFalse();
});

it('skips success hooks and callbacks when deletion is cancelled', function () {
    $service = new class extends UserService
    {
        public array $events = [];

        protected function deleted($model): void
        {
            $this->events[] = 'deleted';
        }
    };
    $user = $service->create(['username' => Str::random()])->model;
    $user->username = User::REJECTED_USERNAME;

    $result = $service->delete($user, function () use ($service) {
        $service->events[] = 'callback';
    });

    expect($result->deleted)->toBeFalse()
        ->and($service->events)->toBeEmpty()
        ->and(User::query()->find($user->id))->not->toBeNull();
});

it('rolls back deletion when the callback fails', function () {
    $service = new UserService();
    $user = $service->create(['username' => Str::random()])->model;

    expect(fn () => $service->delete(
        $user->id,
        fn () => throw new RuntimeException('callback failed')
    ))->toThrow(RuntimeException::class, 'callback failed');

    expect(User::query()->find($user->id))->not->toBeNull();
});
