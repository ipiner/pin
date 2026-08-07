<?php

declare(strict_types=1);

use App\Routes\DummyRoute;
use App\Routes\User\UserRoute;
use Pin\Module\ModuleInspector;

it('generates controller candidates', function ($class, $expected) {
    $result = $this->invoker(ModuleInspector::make($class))->getControllerCandidates();

    expect($result)->toBe($expected);
})->with([
    'with module' => [
        UserRoute::Create,
        [
            'App\\Modules\\User\\UserController',
            'App\\Modules\\User\\User\\UserController',
        ],
    ],
    'without module' => [
        DummyRoute::Index,
        [
            'App\\Modules\\Dummy\\DummyController',
            'App\\Http\\Controllers\\DummyController',
        ],
    ],
]);

it('caches controller result', function () {
    $inspector = ModuleInspector::make('ProductService');

    expect($inspector->controller())->toBe('App\\Http\\Controllers\\ProductController');

    $this->invoker($inspector)->basename = 'OrderService';
    expect($inspector->controller())->toBe('App\\Http\\Controllers\\ProductController');
});
