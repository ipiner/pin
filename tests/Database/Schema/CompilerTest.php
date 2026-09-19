<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Pin\Database\Schema\Compiler;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

it('compiles database schemas', function () {
    $schemas = (new Compiler('testing'))->compile();
    expect($schemas['admins']['name'])->toBe('admins');
});

it('resolves tables database', function (string $driver, ?string $expected) {
    $compiler = new class('s') extends Compiler
    {
        public function getTablesDatabase(): ?string
        {
            return parent::getTablesDatabase();
        }
    };

    config([
        'database.connections.s' => [
            'driver' => $driver,
            'database' => 'testing',
        ],
    ]);

    expect($compiler->getTablesDatabase())->toBe($expected);
})->with([
    ['mysql', 'testing'],
    ['mariadb', 'testing'],
    ['pgsql', null],
    ['sqlite', null],
    ['sqlsrv', null],
]);

it('compiles prefixed tables using their model names', function () {
    config(['database.connections.schema_prefix' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => 'pin_',
    ]]);
    $connection = DB::connection('schema_prefix');
    $connection->getSchemaBuilder()->create('admins', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });
    $connection->statement('create table unrelated (id integer)');

    $tables = (new Compiler('schema_prefix'))->compile();

    expect($tables->keys()->all())->toBe(['admins'])
        ->and($tables['admins']->columns()->keys()->all())->toBe(['id', 'name']);
});
