<?php

declare(strict_types=1);

use Pin\Database\Schema\Column;
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

it('removes only the table suffix from labels', function () {
    $table = new Table(['name' => 'forms', 'comment' => '表单配置表|说明']);

    expect($table->label)->toBe('表单配置');
});

it('supports tables without columns or comments', function () {
    $table = new Table(['name' => 'admins']);

    expect($table->label)->toBe('Admin')
        ->and($table->column('missing'))->toBeNull()
        ->and($table->hasColumn('missing'))->toBeFalse()
        ->and($table->columns()->all())->toBe([])
        ->and($table->attributes())->toBe([]);
});

it('reuses column objects across accessors', function () {
    $existing = new Column(['name' => 'id', 'comment' => '编号']);
    $table = new Table([
        'name' => 'admins',
        'comment' => '',
        'columns' => [
            'id' => $existing,
            'name' => ['name' => 'name', 'comment' => '名称'],
        ],
    ]);
    $column = $table->column('name');
    $column->label = '姓名';

    expect($table->column('id'))->toBe($existing)
        ->and($table->column('name'))->toBe($column)
        ->and($table->columns()['name'])->toBe($column)
        ->and($table->attributes())->toBe(['id' => '编号', 'name' => '姓名']);
});
