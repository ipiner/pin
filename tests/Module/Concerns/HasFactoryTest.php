<?php

declare(strict_types=1);

use App\Routes\DummyRoute;
use App\Routes\User\UserRoute;
use Pin\Module\ModuleInspector;

it('caches factory result', function () {
    $inspector = ModuleInspector::make('DummyService');

    expect($inspector->factory())->toBe('Database\\Factories\\DummyFactory');

    $this->invoker($inspector)->basename = 'OrderService';
    expect($inspector->factory())->toBe('Database\\Factories\\DummyFactory');
});

it('generates factory candidates', function ($class, $expected) {
    $result = $this->invoker(ModuleInspector::make($class))->getFactoryCandidates();

    expect($result)->toBe($expected);
})->with([
    'with module' => [
        UserRoute::Create,
        [
            'Database\\Factories\\User\\UserFactory',
            'Database\\Factories\\UserUserFactory',
            'Database\\Factories\\UserFactory',
        ],
    ],
    'without module' => [
        DummyRoute::Index,
        [
            'Database\\Factories\\DummyFactory',
        ],
    ],
]);
