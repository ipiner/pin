<?php

declare(strict_types=1);

use Pin\Database\Migration;

beforeEach(function () {
    $this->migrationPath = sys_get_temp_dir().'/pin-migrations-'.uniqid();
});

afterEach(function () {
    $this->app['files']->deleteDirectory($this->migrationPath);
});

it('creates migrations with Pin templates', function (string $option, string $method) {
    $this->artisan('make:migration', [
        'name' => 'prepare_widgets_table',
        $option => 'widgets',
        '--path' => $this->migrationPath,
        '--realpath' => true,
    ])->assertSuccessful();

    $paths = $this->app['files']->glob($this->migrationPath.'/*.php');
    expect($paths)->toHaveCount(1);

    $content = $this->app['files']->get($paths[0]);
    expect($content)->toContain("\$this->schema()->{$method}('widgets'")
        ->not->toContain('{{ table }}');
    expect(require $paths[0])->toBeInstanceOf(Migration::class);
})->with([
    'create table' => ['--create', 'create'],
    'update table' => ['--table', 'table'],
]);
