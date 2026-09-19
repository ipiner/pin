<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Pin\IdGenerator\IdGeneratorInterface;
use Pin\IdGenerator\IdGeneratorServiceProvider;

it('declares provided services', function () {
    $provider = new IdGeneratorServiceProvider($this->app);
    $diff = array_diff(
        $provider->provides(),
        [
            'pin.id.timestamp',
            'pin.id.snowflake',
            'pin.id.redis',
        ]
    );
    expect($diff)->toBe([]);
});

it('resolves deferred generators before the application boots', function () {
    $config = $this->app['config'];
    $app = new Application();
    $app->instance('config', $config);
    $services = (new IdGeneratorServiceProvider($app))->provides();
    $app->addDeferredServices(array_fill_keys($services, IdGeneratorServiceProvider::class));

    try {
        foreach ($services as $service) {
            expect($app->make($service))->toBeInstanceOf(IdGeneratorInterface::class)
                ->and($app->make($service))->toBe($app->make($service));
        }
    } finally {
        Application::setInstance($this->app);
    }
});
