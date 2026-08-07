<?php

declare(strict_types=1);

use App\Routes\DummyRoute;
use App\Routes\User\UserRoute;
use Pin\Module\ModuleInspector;

it('generates model candidates', function ($class, $expected) {
    $result = $this->invoker(ModuleInspector::make($class))->getModelCandidates();

    expect($result)->toBe($expected);
})->with([
    'with module' => [
        UserRoute::Create,
        [
            'App\\Modules\\User\\Models\\User',
            'App\\Models\\User\\User',
            'App\\Models\\User\\UserUser',
            'App\\Models\\UserUser',
            'App\\Models\\User',
        ],
    ],
    'without module' => [
        DummyRoute::Index,
        [
            'App\\Models\\Dummy',
        ],
    ],
]);

it('caches model result', function () {
    $inspector = ModuleInspector::make('ProductService');

    expect($inspector->model())->toBe('App\\Models\\Product');

    $this->invoker($inspector)->basename = 'OrderService';
    expect($inspector->model())->toBe('App\\Models\\Product');
});
