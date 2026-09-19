<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Models\User;
use Pin\Auth\UsersProvider;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

it('preserves the Pin model cache when retrieving users by ID', function () {
    $user = UserFactory::new()->create();
    $provider = new UsersProvider(app('hash'), User::class);
    $first = $provider->retrieveById($user->id);

    expect($first?->id)->toBe($user->id)
        ->and($provider->retrieveById((string) $user->id))->toBe($first);
});

it('finds Pin users by ID or username', function () {
    $user = UserFactory::new()->create();
    $provider = new UsersProvider(app('hash'), User::class);

    expect($provider->retrieveById($user->id)?->id)->toBe($user->id)
        ->and($provider->findByUsername($user->username)?->id)->toBe($user->id);
});
