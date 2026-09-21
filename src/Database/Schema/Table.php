<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Pin\Support\DataBag;

/**
 * 数据表结构
 *
 * @property string $name 表名
 * @property string|null $comment 表备注
 * @property string $label 表名称
 * @property array<string, Column|array> $columns 字段集合
 */
class Table extends DataBag
{
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        $this->label = $this->parseLabel();
        $this->columns = array_map($this->resolveColumn(...), $attributes['columns'] ?? []);
    }

    /**
     * 获取字段名称映射
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_map(
            fn (Column|array $column) => $this->resolveColumn($column)->label,
            $this->columns
        );
    }

    /**
     * 获取字段
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
        return collect($this->columns)->map($this->resolveColumn(...));
    }

    /**
     * 是否存在字段
     */
    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * 解析表名称
     */
    protected function parseLabel(): string
    {
        if ($comment = $this->comment ?? null) {
            return Str::replaceEnd('表', '', explode('|', $comment, 2)[0]);
        }

        return Str::headline(Str::singular($this->name));
    }

    /**
     * 转换字段结构
     */
    protected function resolveColumn(Column|array $column): Column
    {
        return $column instanceof Column ? $column : new Column($column);
    }
}
