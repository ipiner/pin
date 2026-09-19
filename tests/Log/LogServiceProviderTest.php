<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Pin\Log\Actor;
use Pin\Log\LogServiceProvider;

it('registers the actor before booting', function () {
    $app = new Application();

    try {
        $app->register(LogServiceProvider::class);

        expect($app->make('pin.log.actor'))->toBeInstanceOf(Actor::class)
            ->and($app->make('pin.log.actor'))->toBe($app->make('pin.log.actor'));
    } finally {
        Application::setInstance($this->app);
    }
});
