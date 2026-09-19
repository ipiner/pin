<?php

declare(strict_types=1);

use Illuminate\Database\MigrationServiceProvider as LaravelMigrationServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Pin\Database\MigrationCreator;
use Pin\Database\MigrationServiceProvider;

it('registers the migration creator', function () {
    $provider = new MigrationServiceProvider($this->app);
    $provider->register();

    expect(app('migration.creator'))->toBeInstanceOf(MigrationCreator::class);
});

it('resolves the deferred creator before booting', function () {
    $app = new Application();
    $app->instance('files', new Filesystem());
    $app->addDeferredServices([
        'migration.creator' => MigrationServiceProvider::class,
        'migrator' => LaravelMigrationServiceProvider::class,
    ]);

    try {
        $creator = $app->make('migration.creator');
        $app->loadDeferredProvider('migrator');

        expect($creator)->toBeInstanceOf(MigrationCreator::class)
            ->and($app->make('migration.creator'))->toBe($creator);
    } finally {
        Application::setInstance($this->app);
    }
});

it('declares provided services', function () {
    $provider = new MigrationServiceProvider($this->app);
    expect($provider->provides())->toBe(['migration.creator']);
});
