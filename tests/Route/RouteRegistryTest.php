<?php

declare(strict_types=1);

use App\Routes\User\UserRoute;
use Illuminate\Support\Facades\Route;
use Pin\Route\RouteRegistry;
use Pin\Route\RouteRegistryItem;

it('can bind and retrieve route', function () {
    $invoker = $this->invoker(RouteRegistry::class);
    $invoker->items = [];

    Route::get('testing');
    UserRoute::registerRoutes();

    $items = RouteRegistry::items();
    /** @var RouteRegistryItem $item */
    $item = $items[UserRoute::Create->name()];
    expect($item->route->getName())->toBe(UserRoute::Create->name());
});
