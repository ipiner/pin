<?php

declare(strict_types=1);

namespace Pin\Debug;

use Illuminate\Support\Str;

/**
 * TypeScript 代码生成器
 */
class TypescriptGenerator
{
    /**
     * 生成模型代码
     */
    public function generate(array $schemas, bool $snakeCase = false): string
    {
        ksort($schemas);
        $snippets = [];

        foreach ($schemas as $table => $schema) {
            $model = Str::studly(Str::singular($table));
            $snippets[] = $this->generateModel($model, $schema['columns'], $snakeCase);
        }

        return implode("\n\n", $snippets);
    }

    /**
     * 生成类型、标签和表格列
     */
    protected function generateModel(string $model, array $columns, bool $snakeCase): string
    {
        ksort($columns);
        $fields = [];
        $labels = [];
        $definitions = [];

        foreach ($columns as $column) {
            if ($column['name'] === 'deleted_at') {
                continue;
            }

            $name = $snakeCase ? $column['name'] : Str::camel($column['name']);
            $type = $this->resolveType($column['type']);
            $comment = preg_replace('/\R/u', ' ', $column['label']);

            $fields[] = "  {$name}: {$type}; // {$comment}";
            $labels[] = "  {$name}: ".$this->quote($column['label']).',';
            $definitions[] = '  table.column('.$this->quote($name).", labels.{$name}),";
        }

        return implode("\n", [
            "export type {$model} = {",
            ...$fields,
            '};',
            '',
            'export const labels = {',
            ...$labels,
            '};',
            '',
            'export const columns = [',
            ...$definitions,
            '];',
        ]);
    }

    /**
     * 映射字段类型
     */
    protected function resolveType(string $type): string
    {
        return preg_match(
            '/^(tinyint|smallint|mediumint|bigint|integer|int|'
            .'decimal|numeric|float|double|real)\b/i',
            $type
        ) ? 'number' : 'string';
    }

    /**
     * 生成字符串字面量
     */
    protected function quote(string $value): string
    {
        return "'".strtr($value, [
            '\\' => '\\\\',
            "'" => "\\'",
            "\r" => '\r',
            "\n" => '\n',
            "\u{2028}" => '\u2028',
            "\u{2029}" => '\u2029',
        ])."'";
    }
}
