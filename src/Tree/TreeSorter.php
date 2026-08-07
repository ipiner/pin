<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;

/**
 * 树结构排序器
 */
class TreeSorter
{
    /**
     * 排序树节点集合
     */
    public function sort(Collection $items): Collection
    {
        return $items->sortBy([
            ['pid', 'asc'],
            ['sort', 'asc'],
            ['id', 'asc'],
        ]);
    }
}
