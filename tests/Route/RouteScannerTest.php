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

it('supports namespace mappings and custom file patterns', function () {
    $scanner = new RouteScanner();
    $path = __DIR__.'/../laravel/app/Routes/User';

    expect($scanner->scan([$path => 'App\\Routes\\User']))->toBe([UserRoute::class])
        ->and($scanner->scan([
            new RouteScanPath($path, '\\App\\Routes\\User\\', 'User*.php'),
        ]))->toBe([UserRoute::class])
        ->and($scanner->scan([]))->toBe([]);
});

it('resolves symbolic links using their scanned paths', function () {
    $path = sys_get_temp_dir().'/pin-route-scan-'.uniqid();
    mkdir($path);

    try {
        symlink(__DIR__.'/../laravel/app/Routes/User/UserRoute.php', $path.'/UserRoute.php');

        expect(new RouteScanner()->scan([$path => 'App\\Routes\\User']))
            ->toBe([UserRoute::class]);
    } finally {
        unlink($path.'/UserRoute.php');
        rmdir($path);
    }
});
