<?php

declare(strict_types=1);

use App\Routes\DummyRoute;
use App\Routes\Order\OrderRoute;
use App\Routes\User\UserRoute;
use Pin\Route\RouteScanner;
use Pin\Route\RouteScanPath;

it('scans and returns all route classes', function () {
    $scanner = new RouteScanner();
    $path = __DIR__.'/../laravel/app/Routes';

    foreach ([
        $path,
        new RouteScanPath($path, 'App\\Routes'),
    ] as $item) {
        $routes = $scanner->scan([$item]);
        expect(count($routes))->toBe(3)
            ->and(in_array(OrderRoute::class, $routes))->toBeTrue()
            ->and(in_array(UserRoute::class, $routes))->toBeTrue()
            ->and(in_array(DummyRoute::class, $routes))->toBeTrue();
    }

    $routes = $scanner->scan([new RouteScanPath($path, 'NotFound')]);
    expect(count($routes))->toBe(0);
});
