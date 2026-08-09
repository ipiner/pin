<?php

declare(strict_types=1);

use App\Modules\User\Actions\CreateUserAction;
use App\Modules\User\Actions\ListUsersAction;
use App\Routes\DummyRoute;
use App\Routes\User\UserRoute;
use Pin\Action\Action;
use Pin\Support\Facades\RuntimeCache;

afterEach(function () {
    RuntimeCache::flush();
});

it('resolves action class name', function () {
    $t = UserRoute::Index->testing($this);
    $invoker = $this->invoker($t);

    expect($invoker->actionClass)
        ->toBe(ListUsersAction::class)
        ->and($invoker->hasAction())
        ->toBeTrue();

    $t->withAction(CreateUserAction::class);
    expect($invoker->actionClass)
        ->toBe(CreateUserAction::class)
        ->and($invoker->hasAction())
        ->toBeTrue;
});

it('resolves action instance', function () {
    $t = UserRoute::Create->testing($this);
    $invoker = $this->invoker($t);

    expect($invoker->actionClass)
        ->toBe(CreateUserAction::class)
        ->and($invoker->hasAction())
        ->toBeTrue()
        ->and($invoker->action())
        ->toBeInstanceOf(Action::class);
});

it('throws exception when action class does not exist', function () {
    $this->invoker(DummyRoute::Index->testing($this))->action();
})->throws('App\\Actions\\Dummy\\IndexAction');
