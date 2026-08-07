<?php

declare(strict_types=1);

use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Debug\DebugRoute;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

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
            "table.column('createdAt', labels.createdAt)",
        ], false)
        ->assertDontSee(['deletedAt', 'created_at', 'deleted_at'], false);

    $this->get(DebugRoute::GenerateTypescript->route([
        'connection' => 'testing', 'snake_case' => 1,
    ]))
        ->assertSee([
            'export type User',
            'created_at: string',
            "table.column('created_at', labels.created_at)",
        ], false)
        ->assertDontSee(['createdAt'], false);
});
