<?php

declare(strict_types=1);

use App\Routes\DummyRoute;
use App\Routes\User\UserRoute;

it('resolves route definition into route metadata', function () {
    expect(UserRoute::List->method())->toBe('GET')
        ->and(UserRoute::List->uri())->toBe('/api/users')
        ->and(UserRoute::List->name())->toBe('users');

    expect(DummyRoute::Index->uri())->toBe('/api/prefix/dummy');
});
