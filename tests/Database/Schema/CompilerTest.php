<?php

declare(strict_types=1);

use Pin\Database\Schema\Compiler;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

it('compiles database schemas', function () {
    $schemas = (new Compiler('testing'))->compile();
    expect($schemas['admins']['name'])->toBe('admins');
});

it('resolves tables database', function () {
    $compiler = new class('s') extends Compiler
    {
        public function getTablesDatabase(): ?string
        {
            return parent::getTablesDatabase();
        }
    };

    config([
        'database.connections.s' => [
            'driver' => 'mysql',
            'database' => 'testing',
        ],
    ]);

    expect($compiler->getTablesDatabase())->toBe('testing');
});
