<?php

declare(strict_types=1);

use Pin\Auth\ConsoleUser;
use Pin\Support\Facades\Actor;
use Pin\Tests\Models\Models\User;

it('returns current authenticated user or null when no user exists', function () {
    expect(Actor::id())->toBe((new ConsoleUser())->id);
    expect(Actor::username())->toBe((new ConsoleUser())->username);

    auth()->setUser(new User(['id' => 1]));
    expect(Actor::id())->toBe(1);

    auth()->forgetUser();
    app()->request->server->set('argv', null);

    expect(Actor::user())->toBeNull();
});

it('returns correct user type', function () {
    expect(Actor::type())->toBe('console');

    auth()->setUser(new User(['id' => 1]));
    expect(Actor::type())->toBe('user');
});
