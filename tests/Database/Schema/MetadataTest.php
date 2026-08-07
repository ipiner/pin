<?php

declare(strict_types=1);

use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Database\Schema\Metadata;
use Pin\Support\Facades\RuntimeCache;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\Admin;

uses(InteractsWithDatabase::class);

it('loads metadata', function () {
    $command = $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing', '--force' => true]
    );

    $command->assertExitCode(0);

    RuntimeCache::flush();

    $meta = Metadata::make('testing', 'admins');
    expect($meta->label)->toBe('Admin');

    $meta = Metadata::make(Admin::class);
    expect($meta->attributes['created_at'])->toBe('Created At');
});
