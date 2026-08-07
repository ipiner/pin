<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Collection;

/**
 * TreeNavigation
 *
 * 提供基于节点的“树形语义访问能力”。
 */
trait TreeNavigation
{
    /**
     * 获取当前节点的所有祖先节点（不包含自身）
     *
     * 返回顺序：从根节点 → 父节点（正序）
     *
     * @return Collection 祖先节点集合（按层级顺序）
     */
    public function ancestors(): Collection
    {
        $ids = $this->paths();
        array_pop($ids); // 移除自身 ID

        $collection = new Collection();
        if (! $ids) {
            return $collection;
        }

        $items = static::findMany($ids);
        foreach ($ids as $id) {
            if (isset($items[$id])) {
                $collection->push($items[$id]);
            }
        }

        return $collection;
    }

    /**
     * 获取当前节点的整个子树（包含所有后代节点）
     *
     * @return Collection 后代节点集合
     */
    public function descendants(): Collection
    {
        return static::descendantsOf($this->path);
    }

    /**
     * 获取指定节点的所有后代节点（子树）
     *
     * @param  string  $path  当前节点路径
     * @return Collection 后代节点集合
     */
    protected static function descendantsOf(string $path): Collection
    {
        return static::where('path', 'like', $path.'/%')->get();
    }
}
