<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * 树路径展示
 */
trait TreePresenter
{
    /**
     * 获取完整名称
     *
     * @return Attribute<string, never>
     */
    public function fullName(): Attribute
    {
        return Attribute::get(fn () => $this->namePath());
    }

    /**
     * 获取名称路径
     */
    public function namePath(?string $separator = ' / '): array|string
    {
        $ids = $this->paths();

        if (! $ids) {
            return $separator === null ? [] : '';
        }

        $items = static::findMany($ids);
        $names = array_map(
            fn ($id) => $items->get($id)?->name ?? '不存在或已删除',
            $ids,
        );

        return $separator === null ? $names : implode($separator, $names);
    }
}
