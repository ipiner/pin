<?php

declare(strict_types=1);

use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Database\Schema\Metadata;
use Pin\Support\Facades\RuntimeCache;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\Admin;

uses(InteractsWithDatabase::class);

beforeEach(function () {
    $this->databasePath = $this->app->databasePath();
    $this->app->useDatabasePath(sys_get_temp_dir().'/pin-metadata-'.uniqid());
    RuntimeCache::flush();
});

afterEach(function () {
    $this->app['files']->deleteDirectory($this->app->databasePath());
    $this->app->useDatabasePath($this->databasePath);
    RuntimeCache::flush();
});

it('loads metadata', function () {
    $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing', '--force' => true]
    )->assertSuccessful()->run();

    RuntimeCache::flush();

    $meta = Metadata::make('testing', 'admins');
    expect($meta->label)->toBe('Admin');

    $meta = Metadata::make(Admin::class);
    expect($meta->attributes['created_at'])->toBe('Created At');
});

it('keeps connection and table cache keys distinct', function () {
    $first = Metadata::make('schema_a', 'bc');
    $second = Metadata::make('schema_ab', 'c');

    expect($first->label)->toBe('Bc')
        ->and($second->label)->toBe('C')
        ->and($first)->not->toBe($second)
        ->and(Metadata::make('schema_a', 'bc'))->toBe($first);
});

it('creates and caches metadata subclasses separately', function () {
    $base = Metadata::make('schema', 'missing');
    $subclass = new class('schema', 'missing') extends Metadata
    {
    };
    $metadata = $subclass::make('schema', 'missing');

    expect($metadata)->toBeInstanceOf($subclass::class)
        ->and($metadata)->not->toBe($base)
        ->and($subclass::make('schema', 'missing'))->toBe($metadata);
});
