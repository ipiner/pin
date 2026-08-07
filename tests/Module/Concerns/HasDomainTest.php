<?php

declare(strict_types=1);

use Pin\Module\ModuleInspector;

it('extracts domain from class name', function ($class, $expected) {
    $inspector = ModuleInspector::make($class);

    expect($inspector->domain())->toBe($expected);
})->with([
    ['CreateAction', 'Action'],
    ['CreateUserAction', 'User'],
    ['UserService', 'User'],
    ['UserRoute', 'User'],
]);

it('caches domain result', function () {
    $inspector = ModuleInspector::make('UserService');

    expect($inspector->domain())->toBe('User');

    $this->invoker($inspector)->basename = 'OrderService';
    expect($inspector->domain())->toBe('User');
});
