<?php

declare(strict_types=1);

use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Database\Schema\Metadata;
use Pin\Support\Facades\RuntimeCache;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

it('generates table schemas files', function () {
    RuntimeCache::flush();

    $command = $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing', '--force' => true]
    );

    $command->assertExitCode(0);
    $command->expectsOutput('Written schemas: '.database_path('schemas/testing/__schemas__.php'));
    $command->expectsOutput('Written attributes: '.database_path('schemas/testing/__attributes__.php'));
    $command->expectsOutput('Written table file: '.database_path('schemas/testing/admins.php'));

    // force -> false
    $command = $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing']
    );

    $command->assertExitCode(0);
    $command->expectsOutput('Written schemas: '.database_path('schemas/testing/__schemas__.php'));
    $command->expectsOutput('Written attributes: '.database_path('schemas/testing/__attributes__.php'));
    $command->doesntExpectOutput('Written table file: '.database_path('schemas/testing/admins.php'));

    $schema = new Metadata('testing', 'admins');

    expect($schema->label)->toBe('Admin')
        ->and($schema->attributes['created_at'])->toBe('Created At');
});
