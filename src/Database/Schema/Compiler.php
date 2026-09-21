<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 数据库结构编译器
 */
class Compiler
{
    protected Builder $schema;

    public function __construct(protected string $connection = 'default')
    {
    }

    /**
     * 编译数据库结构
     *
     * @return Collection<string, Table>
     */
    public function compile(): Collection
    {
        return collect($this->getTables())
            ->map($this->buildTableSchema(...))
            ->keyBy('name');
    }

    /**
     * 构建数据表结构
     */
    protected function buildTableSchema(array $table): Table
    {
        $name = Str::chopStart(
            $table['name'],
            $this->schema()->getConnection()->getTablePrefix()
        );

        return new Table([
            'name' => $name,
            'comment' => $table['comment'],
            'columns' => $this->getColumns($name),
        ]);
    }

    /**
     * 获取字段结构
     *
     * @return array<string, Column>
     */
    protected function getColumns(string $table): array
    {
        $columns = [];

        foreach ($this->schema()->getColumns($table) as $column) {
            $columns[$column['name']] = new Column($column);
        }

        return $columns;
    }

    /**
     * 获取数据库表列表
     *
     * @return list<array{name: string, comment: string|null}>
     */
    protected function getTables(): array
    {
        $tables = $this->schema()->getTables($this->getTablesDatabase());
        $prefix = $this->schema()->getConnection()->getTablePrefix();

        if (! $prefix) {
            return $tables;
        }

        return array_values(array_filter(
            $tables,
            static fn (array $table) => str_starts_with($table['name'], $prefix)
        ));
    }

    /**
     * 获取表查询的数据库名
     */
    protected function getTablesDatabase(): ?string
    {
        return $this->requiresDatabaseParameter()
            ? config("database.connections.{$this->connection}.database")
            : null;
    }

    /**
     * 是否需要指定数据库名
     */
    protected function requiresDatabaseParameter(): bool
    {
        $driver = config('database.connections.'.$this->connection.'.driver');

        return in_array($driver, ['mysql', 'mariadb'], true);
    }

    /**
     * 获取结构构建器
     */
    protected function schema(): Builder
    {
        return $this->schema ??= Schema::connection($this->connection);
    }
}
