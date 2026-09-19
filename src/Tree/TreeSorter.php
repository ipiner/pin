<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;

/**
 * 树节点排序。
 */
class TreeSorter
{
    /**
     * 按父节点、排序值和 ID 排序。
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
