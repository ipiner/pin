<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Pin\Support\DataBag;

/**
 * 数据表 Schema DTO。
 *
 * @property string $name 表名
 * @property string|null $comment 表备注
 * @property string|null $label 表展示名称，由 comment 自动解析生成（派生字段）
 * @property array<string, array> $columns 原始字段集合
 */
class Table extends DataBag
{
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
        $this->label = $this->parseLabel();
    }

    /**
     * 字段 label 映射。
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->columns()
            ->map(fn (Column $column) => $column->label)
            ->toArray();
    }

    /**
     * 获取字段。
     */
    public function column(string $name): ?Column
    {
        $column = $this->columns[$name] ?? null;

        return $column === null ? null : $this->resolveColumn($column);
    }

    /**
     * 获取所有字段
     *
     * @return Collection<string, Column>
     */
    public function columns(): Collection
    {
        return collect($this->columns)->map(
            fn ($item) => $this->resolveColumn($item)
        );
    }

    /**
     * 是否存在字段
     */
    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * label 派生
     *
     * comment → label fallback
     */
    protected function parseLabel(): string
    {
        return $this->comment
            ? str_replace('表', '', explode('|', $this->comment)[0])
            : Str::headline(Str::singular($this->name));
    }

    /**
     * Column 标准化。
     */
    protected function resolveColumn(Column|array $column): Column
    {
        return $column instanceof Column ? $column : new Column($column);
    }
}
