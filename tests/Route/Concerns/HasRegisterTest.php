<?php

declare(strict_types=1);

use App\Modules\User\UserController;
use App\Routes\Order\OrderRoute;
use App\Routes\User\UserRoute;
use Pin\Route\Routable;

it('registers all enum routes', function () {
    OrderRoute::registerRoutes();

    foreach (OrderRoute::cases() as $route) {
        $route->testJson($this)->assertMessage($route->name());
    }
});

it('resolves route controller', function (Routable $route, string $expected) {
    expect($this->invoker($route)->controller())->toBe($expected);
})->with([
    'OrderRoute::Index' => [OrderRoute::Index, 'App\\Modules\\Order\\Order\\OrderController'],
    'OrderRoute::Create' => [OrderRoute::Create, 'App\\Modules\\Order\\Order\\OrderController'],
    'UserRoute::Create' => [UserRoute::Create, UserController::class],
]);

it('resolves route handler', function () {
    expect($this->invoker(UserRoute::Create)->handler())->toBe(
        [UserController::class, 'create']
    );
    expect($this->invoker(UserRoute::Handler)->handler())->toBe(
        ['UserHandler', 'handle']
    );

    expect($this->invoker(OrderRoute::Create)->handler())->toBeInstanceOf(Closure::class);
});
