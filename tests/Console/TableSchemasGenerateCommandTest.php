<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Database\Schema\Column;
use Pin\Database\Schema\Compiler;
use Pin\Database\Schema\Metadata;
use Pin\Database\Schema\Table;
use Pin\Support\Facades\RuntimeCache;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

beforeEach(function () {
    $this->databasePath = $this->app->databasePath();
    $this->app->useDatabasePath(sys_get_temp_dir().'/pin-console-'.uniqid());
    RuntimeCache::flush();
});

afterEach(function () {
    $this->app['files']->deleteDirectory($this->app->databasePath());
    $this->app->useDatabasePath($this->databasePath);
    RuntimeCache::flush();
});

it('generates table schemas files', function () {
    $command = $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing', '--force' => true]
    );

    $command->assertExitCode(0);
    $command->expectsOutput('Written schemas: '.database_path('schemas/testing/__schemas__.php'));
    $command->expectsOutput(
        'Written attributes: '.database_path('schemas/testing/__attributes__.php'),
    );
    $command->expectsOutput('Written table file: '.database_path('schemas/testing/admins.php'));
    $command->run();

    $command = $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing']
    );

    $command->assertExitCode(0);
    $command->expectsOutput('Written schemas: '.database_path('schemas/testing/__schemas__.php'));
    $command->expectsOutput(
        'Written attributes: '.database_path('schemas/testing/__attributes__.php'),
    );
    $command->doesntExpectOutput(
        'Written table file: '.database_path('schemas/testing/admins.php'),
    );
    $command->run();

    $schema = new Metadata('testing', 'admins');

    expect($schema->label)->toBe('Admin')
        ->and($schema->attributes['created_at'])->toBe('Created At');
});

it('generates loadable schemas with quotes and backslashes', function (string $name) {
    $column = new Column([
        'name' => 'name',
        'comment' => "Owner's name",
        'default' => "O'Reilly\\archive",
        'nullable' => false,
        'auto_increment' => false,
        'generation' => null,
    ]);
    $table = new Table([
        'name' => $name,
        'comment' => "Owner's \\\\archive",
        'columns' => ['name' => $column],
    ]);
    $compiler = Mockery::mock(Compiler::class);
    $compiler->shouldReceive('compile')->once()->andReturn(collect([$name => $table]));
    $this->app->bind(Compiler::class, fn () => $compiler);

    $this->artisan(TableSchemasGenerateCommand::class, ['--connection' => 'testing'])
        ->assertSuccessful()->run();

    $directory = database_path('schemas/testing');
    $schemas = require $directory.'/__schemas__.php';
    $attributes = require $directory.'/__attributes__.php';
    $schema = require $directory.'/'.$name.'.php';

    expect($schemas[$name]['name'])->toBe($name)
        ->and($schemas[$name]['columns']['name'])->toBe($column->toArray())
        ->and($schema)->toBe([
            'label' => $schemas[$name]['label'],
            'attributes' => $attributes[$name],
        ]);
})->with(["owner's_logs", 'archived\\\\logs']);

it('preserves custom schemas until forced while refreshing metadata', function () {
    $options = ['--connection' => 'testing'];
    $this->artisan(TableSchemasGenerateCommand::class, $options)->assertSuccessful()->run();

    $directory = database_path('schemas/testing');
    $file = $directory.'/admins.php';
    $generated = file_get_contents($file);
    $custom = "<?php return ['label' => 'Custom admin', 'attributes' => ['custom' => 'Custom']];";
    file_put_contents($file, $custom);
    file_put_contents($directory.'/__schemas__.php', '<?php return [];');
    file_put_contents($directory.'/__attributes__.php', '<?php return [];');

    $this->artisan(TableSchemasGenerateCommand::class, $options)->assertSuccessful()->run();

    $schemas = require $directory.'/__schemas__.php';
    $attributes = require $directory.'/__attributes__.php';

    expect(file_get_contents($file))->toBe($custom)
        ->and($schemas['admins']['columns']['id'])->toBeArray()
        ->and($attributes['admins']['created_at'])->toBe('Created At');

    $this->artisan(TableSchemasGenerateCommand::class, [...$options, '--force' => true])
        ->assertSuccessful()->run();

    expect(file_get_contents($file))->toBe($generated);
});

it('stops when a schema file cannot be written', function () {
    $file = database_path('schemas/testing/__schemas__.php');
    $files = Mockery::mock(Filesystem::class)->makePartial();
    $files->shouldReceive('put')->once()->with($file, Mockery::type('string'))->andReturn(false);
    $this->app->instance('files', $files);

    expect(fn () => $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing'],
    )->run())->toThrow(RuntimeException::class, 'Unable to write schema file: '.$file);

    expect(is_file(database_path('schemas/testing/__attributes__.php')))->toBeFalse()
        ->and(is_file(database_path('schemas/testing/admins.php')))->toBeFalse();
});
