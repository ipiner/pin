<?php

declare(strict_types=1);

use Pin\Debug\TypescriptGenerator;

it('generates sorted model snippets', function (bool $snakeCase, string $field) {
    $schemas = [
        'users' => ['columns' => [
            'user_name' => ['name' => 'user_name', 'type' => 'varchar', 'label' => '用户名'],
            'id' => ['name' => 'id', 'type' => 'int unsigned', 'label' => 'ID'],
            'deleted_at' => ['name' => 'deleted_at', 'type' => 'datetime', 'label' => '删除时间'],
        ]],
        'admins' => ['columns' => []],
    ];

    $output = (new TypescriptGenerator())->generate($schemas, $snakeCase);

    expect($output)->toStartWith('export type Admin = {')
        ->toContain("export type User = {\n  id: number; // ID\n  {$field}: string; // 用户名")
        ->toContain("  {$field}: '用户名',")
        ->toContain("table.column('{$field}', labels.{$field})")
        ->not->toContain('deleted_at', 'deletedAt');
})->with([
    [false, 'userName'],
    [true, 'user_name'],
]);

it('maps database types', function (string $type, string $expected) {
    $schemas = ['users' => ['columns' => [
        'value' => ['name' => 'value', 'type' => $type, 'label' => '值'],
    ]]];

    expect((new TypescriptGenerator())->generate($schemas))
        ->toContain("  value: {$expected};");
})->with([
    ['bigint unsigned', 'number'],
    ['INTEGER', 'number'],
    ['decimal(10,2)', 'number'],
    ['float', 'number'],
    ['double precision', 'number'],
    ['numeric', 'number'],
    ['real', 'number'],
    ['point', 'string'],
    ['varchar(255)', 'string'],
    ['datetime', 'string'],
]);

it('escapes labels in strings and comments', function () {
    $label = "Owner's \\archive\r\nnext\u{2028}line";
    $schemas = ['users' => ['columns' => [
        'name' => ['name' => 'name', 'type' => 'varchar', 'label' => $label],
    ]]];

    $output = (new TypescriptGenerator())->generate($schemas);

    expect($output)->toContain("name: 'Owner\\'s \\\\archive\\r\\nnext\\u2028line',")
        ->toContain("name: string; // Owner's \\archive next line")
        ->not->toContain("\r", "\u{2028}");
});
