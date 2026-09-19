<?php

declare(strict_types=1);

use App\Modules\User\Actions\CreateUserAction;
use App\Routes\DummyRoute;
use App\Routes\User\UserRoute;
use Pin\Module\ModuleInspector;
use Pin\Support\Facades\RuntimeCache;

beforeEach(function () {
    RuntimeCache::flush();
});

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

it('isolates actions for routes with the same value', function () {
    expect(DummyRoute::Index->value)->toBe(UserRoute::Index->value)
        ->and(ModuleInspector::make(DummyRoute::Index)->action(DummyRoute::Index))
        ->toBe('App\\Actions\\Dummy\\IndexAction')
        ->and(ModuleInspector::make(UserRoute::Index)->action(UserRoute::Index))
        ->toBe('App\\Modules\\User\\Actions\\IndexAction');
});

it('isolates action results by inspector', function () {
    expect((new ModuleInspector('ProductService'))->action(UserRoute::Create))
        ->toBe('App\\Actions\\Product\\CreateAction')
        ->and((new ModuleInspector('OrderService'))->action(UserRoute::Create))
        ->toBe('App\\Actions\\Order\\CreateAction');
});

it('keeps the first existing action result', function () {
    $inspector = new ModuleInspector(UserRoute::class);

    expect($inspector->action(UserRoute::Create))
        ->toBe(CreateUserAction::class)
        ->and($inspector->action(UserRoute::Update))
        ->toBe('App\\Modules\\User\\Actions\\UpdateAction');
});
