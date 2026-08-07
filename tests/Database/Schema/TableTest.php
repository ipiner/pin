<?php

declare(strict_types=1);

use Pin\Database\Schema\Table;

it('resolves table metadata', function () {
    expect((new Table([
        'name' => 'admins',
        'comment' => '',
    ]))->label)->toBe('Admin');

    $table = new Table([
        'name' => 'admins',
        'comment' => '管理员表',
        'columns' => [
            'created_at' => [
                'name' => 'created_at',
                'comment' => '',
            ],
        ],
    ]);

    expect($table->label)->toBe('管理员')
        ->and($table->hasColumn('created_at'))->toBeTrue()
        ->and($table->hasColumn('id'))->toBeFalse()
        ->and($table->column('created_at')->label)->toBe('Created At')
        ->and($table->columns()['created_at']->label)->toBe('Created At')
        ->and($table->attributes()['created_at'])->toBe('Created At');
});
