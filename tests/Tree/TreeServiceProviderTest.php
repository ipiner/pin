<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Pin\Tree\TreeServiceProvider;

it('declares provided services', function () {
    $provider = new TreeServiceProvider($this->app);
    expect($provider->provides())->toBe([
        'pin.tree',
        'pin.tree.checker',
        'pin.tree.filter',
        'pin.tree.sorter',
    ]);
});

it('registers tree singletons before application boot', function () {
    $container = new Container();
    $provider = new TreeServiceProvider($container);
    $provider->register();

    foreach ($provider->provides() as $service) {
        expect($container->bound($service))->toBeTrue()
            ->and($container->make($service))->toBe($container->make($service));
    }
});
