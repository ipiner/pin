<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Collection;

/**
 * 树节点导航。
 */
trait TreeNavigation
{
    /**
     * 从根到父节点获取祖先。
     *
     * @return Collection<int, static>
     */
    public function ancestors(): Collection
    {
        $ids = $this->paths();
        array_pop($ids);

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
     * 获取后代节点。
     *
     * @return Collection<int, static>
     */
    public function descendants(): Collection
    {
        return static::descendantsOf($this->path);
    }

    /**
     * 按路径获取后代节点。
     *
     * @return Collection<int, static>
     */
    protected static function descendantsOf(string $path): Collection
    {
        return static::where('path', 'like', $path.'/%')->get();
    }
}
