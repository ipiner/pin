<?php

declare(strict_types=1);

use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Debug\DebugRoute;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

beforeEach(function () {
    $this->databasePath = $this->app->databasePath();
    $this->app->useDatabasePath(sys_get_temp_dir().'/pin-debug-'.uniqid());
});

afterEach(function () {
    $this->app['files']->deleteDirectory($this->app->databasePath());
    $this->app->useDatabasePath($this->databasePath);
});

it('returns config data', function ($key, $expected) {
    $config = DebugRoute::Config->testing($this)
        ->withRouteParams(['key' => $key])
        ->json()->json('data');

    expect(array_diff($expected, array_keys($config)))->toBeEmpty();
})->with([
    [null, ['app', 'cache', 'database']],
    ['app', ['name', 'env', 'x_api_document']],
]);

it('returns debug request id, included file count and file list', function () {
    DebugRoute::Index->testJson($this)->assertJsonStructure([
        'data' => [
            'request_id',
            'count',
            'files',
        ],
    ]);
});

it('gets errors successfully', function () {
    DebugRoute::Errors->testJson($this)->assertJsonStructure([
        'data' => [
            '*' => [
                'code',
                'status',
                'message',
            ],
        ],
    ]);
});

it('gets registered routes successfully', function () {
    DebugRoute::Routes->testJson($this)->assertJsonStructure([
        'data' => [
            '*' => [
                'name',
                'action',
                'case',
                'title',
            ],
        ],
    ]);
});

it('returns phpinfo output as string', function () {
    $this->get(DebugRoute::Phpinfo->route())
        ->assertSee('PHP Version');
    $this->get(DebugRoute::Phpinfo->route(['flag' => INFO_CREDITS]))
        ->assertDontSee('PHP Version');
});

it('generates typescript interfaces from database schema', function () {
    $this->artisan(
        TableSchemasGenerateCommand::class,
        ['--connection' => 'testing']
    );
    $this->get(DebugRoute::GenerateTypescript->route(['connection' => 'testing']))
        ->assertSee([
            'export type User',
            'createdAt: string',
            e("table.column('createdAt', labels.createdAt)"),
        ], false)
        ->assertDontSee(['deletedAt', 'created_at', 'deleted_at'], false);

    $this->get(DebugRoute::GenerateTypescript->route([
        'connection' => 'testing', 'snake_case' => 1,
    ]))
        ->assertSee([
            'export type User',
            'created_at: string',
            e("table.column('created_at', labels.created_at)"),
        ], false)
        ->assertDontSee(['createdAt'], false);
});

it('returns not found when schema files are missing', function () {
    $this->getJson(DebugRoute::GenerateTypescript->route(['connection' => 'missing']))
        ->assertNotFound();
});

it('rejects schema paths outside the connection directory', function (string $connection) {
    $this->app['files']->ensureDirectoryExists(database_path('schemas/testing'));
    file_put_contents(database_path('schemas/__schemas__.php'), '<?php return [];');
    file_put_contents(database_path('__schemas__.php'), '<?php return [];');

    $this->getJson(DebugRoute::GenerateTypescript->route(['connection' => $connection]))
        ->assertNotFound();
})->with(['..', 'testing/..']);

it('renders schema labels as text', function () {
    $label = '<script>alert("label")</script>&lt;b&gt;';
    $schemas = ['users' => ['columns' => [
        'name' => ['name' => 'name', 'type' => 'varchar', 'label' => $label],
    ]]];
    $this->app['files']->ensureDirectoryExists(database_path('schemas/testing'));
    file_put_contents(
        database_path('schemas/testing/__schemas__.php'),
        '<?php return '.var_export($schemas, true).';'
    );

    $response = $this->get(DebugRoute::GenerateTypescript->route(['connection' => 'testing']));

    $response->assertOk()->assertSee(e($label), false)->assertDontSee('<script>', false);
    expect(html_entity_decode($response->getContent(), ENT_QUOTES, 'UTF-8'))->toContain($label);
});
