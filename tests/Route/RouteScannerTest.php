<?php

declare(strict_types=1);

use App\Routes\DummyRoute;
use App\Routes\Order\OrderRoute;
use App\Routes\User\UserRoute;
use Pin\Route\RouteScanner;

it('scans and returns all route classes', function () {
    $scanner = new class extends RouteScanner
    {
        protected function resolveClassFromFile(SplFileInfo $file): ?string
        {
            $enum = parent::resolveClassFromFile($file);
            if (! enum_exists($enum)) {
                $enum = str_replace(
                    [realpath(__DIR__.'/../laravel/'), '/'],
                    ['', '\\'],
                    substr($file->getRealPath(), 0, -4)
                );

                return ucfirst(trim($enum, '\\'));
            }

            return $enum;
        }
    };
    //    $scanner = new RouteScanner();
    $routes = $scanner->scan([__DIR__.'/../laravel/app/Routes']);

    expect(count($routes))->toBe(3)
        ->and(in_array(OrderRoute::class, $routes))->toBeTrue()
        ->and(in_array(UserRoute::class, $routes))->toBeTrue()
        ->and(in_array(DummyRoute::class, $routes))->toBeTrue();
});
