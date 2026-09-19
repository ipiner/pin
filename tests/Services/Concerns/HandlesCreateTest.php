<?php

declare(strict_types=1);

use App\Services\UserService;
use Illuminate\Support\Str;
use Pin\Errors\Errors;
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

it('rejects cancelled creation without running success hooks or callbacks', function () {
    $service = new class extends UserService
    {
        public array $events = [];

        protected function creating(array &$data): void
        {
            User::create(['username' => 'creation-side-effect']);
        }

        protected function created($model, array $data): void
        {
            $this->events[] = 'created';
        }

        protected function saved($model, array $data): void
        {
            $this->events[] = 'saved';
        }
    };

    expect(fn () => $service->create(
        ['username' => User::REJECTED_USERNAME],
        function () use ($service) {
            $service->events[] = 'callback';
        }
    ))->toThrow(Errors::CreateFailed->exception());

    expect($service->events)->toBeEmpty()
        ->and(User::query()->count())->toBe(0);
});

it('rolls back creation when the callback fails', function () {
    $service = new UserService();

    expect(fn () => $service->create(
        ['username' => 'rollback-create'],
        fn () => throw new RuntimeException('callback failed')
    ))->toThrow(RuntimeException::class, 'callback failed');

    expect(User::query()->where('username', 'rollback-create')->exists())->toBeFalse();
});
