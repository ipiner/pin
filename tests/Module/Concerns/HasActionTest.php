<?php

declare(strict_types=1);

use App\Routes\DummyRoute;
use App\Routes\User\UserRoute;
use Pin\Module\ModuleInspector;

it('generates action candidates', function ($route, $expected) {
    $result = $this->invoker(ModuleInspector::make($route))->getActionCandidates($route);

    expect($result)->toBe($expected);
})->with([
    'with module route' => [
        UserRoute::Create,
        [
            'App\\Modules\\User\\User\\Actions\\CreateUserAction',
            'App\\Modules\\User\\User\\Actions\\CreateAction',
            'App\\Modules\\User\\Actions\\CreateUserAction',
            'App\\Modules\\User\\Actions\\CreateAction',
        ],
    ],
    'without module route' => [
        DummyRoute::Index,
        [
            'App\\Modules\\Dummy\\DummyAction',
            'App\\Actions\\DummyAction',
            'App\\Actions\\Dummy\\IndexDummyAction',
            'App\\Actions\\Dummy\\IndexAction',
        ],
    ],
]);
