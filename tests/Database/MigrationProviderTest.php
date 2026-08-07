<?php

declare(strict_types=1);

use Pin\Database\MigrationCreator;
use Pin\Database\MigrationServiceProvider;

it('boots the migration provider and registers migration.creator', function () {
    $provider = new MigrationServiceProvider($this->app);
    $provider->boot();

    expect(app('migration.creator'))->toBeInstanceOf(MigrationCreator::class);
});

it('declares provided services', function () {
    $provider = new MigrationServiceProvider($this->app);
    expect($provider->provides())->toBe(['migration.creator']);
});
