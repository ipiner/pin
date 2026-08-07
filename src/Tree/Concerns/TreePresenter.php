<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * TreePresenter
 *
 * 树形结构的“展示层（Presentation Layer）”，将 path 结构转换为用户可读的展示数据。
 */
trait TreePresenter
{
    /**
     * fullName 访问器
     *
     * @return Attribute<string>
     */
    public function fullName(): Attribute
    {
        return Attribute::get(
            fn () => $this->namePath()
        );
    }

    /**
     * 将 path 转换为“名称路径字符串或数组”
     */
    public function namePath(?string $separator = ' / '): array|string
    {
        $ids = explode('/', $this->path);
        $items = static::findMany($ids);
        $names = collect($ids)->map(
            fn ($id) => $items[$id]->name ?? '不存在或已删除'
        );

        return $separator === null ? $names->toArray() : $names->join($separator);
    }
}
