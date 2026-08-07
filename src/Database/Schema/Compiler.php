<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * 数据库 schema 编译器（DTO 转换层）。
 */
class Compiler
{
    public function __construct(protected string $connection = 'default')
    {
    }

    /**
     * 编译数据库 schema。
     *
     * @return Collection<Table>
     */
    public function compile(): Collection
    {
        return collect($this->getTables())
            ->map(fn ($table) => $this->buildTableSchema($table))
            ->keyBy('name');
    }

    /**
     * 构建 Table DTO。
     */
    protected function buildTableSchema(array $table): Table
    {
        return new Table([
            'name' => $table['name'],

            // 表注释（DB comment）
            // 通常用于生成 label / description
            'comment' => $table['comment'],

            // 字段集合
            // key: column name
            // value: Column DTO
            'columns' => $this->getColumns($table['name']),
        ]);
    }

    /**
     * 获取字段结构。
     *
     *  Schema driver 返回原始结构 → Column DTO。
     *
     * @return array<string, Column>
     */
    protected function getColumns(string $table): array
    {
        $columns = [];

        foreach (Schema::connection($this->connection)->getColumns($table) as $item) {
            /**
             * Column DTO
             * 将原始 schema array 转换为统一对象结构
             */
            $columns[$item['name']] = new Column([
                'name' => $item['name'],

                /**
                 * 数据类型信息
                 */
                'type_name' => $item['type_name'],
                'type' => $item['type'],

                /**
                 * 约束信息
                 */
                'nullable' => $item['nullable'],
                'default' => $item['default'],
                'auto_increment' => $item['auto_increment'],

                /**
                 * 字符集 / 生成列信息
                 */
                'collation' => $item['collation'],
                'generation' => $item['generation'],

                /**
                 * DB comment（用于生成 UI label / description）
                 */
                'comment' => $item['comment'],
            ]);
        }

        return $columns;
    }

    /**
     * 获取数据库表列表。
     *
     * @return array<int, array{name:string, comment?:string}>
     */
    protected function getTables(): array
    {
        return Schema::connection($this->connection)->getTables(
            $this->getTablesDatabase()
        );
    }

    /**
     * database 参数策略（driver 差异）。
     */
    protected function getTablesDatabase(): ?string
    {
        return $this->requiresDatabaseParameter()
            ? config("database.connections.{$this->connection}.database")
            : null;
    }

    /**
     * driver 是否需要 database 参数。
     */
    protected function requiresDatabaseParameter(): bool
    {
        $driver = config('database.connections.'.$this->connection.'.driver');

        return ! in_array($driver, [
            'sqlite',
            'sqlsrv',
        ]);
    }
}
