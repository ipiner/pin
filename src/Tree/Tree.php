<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;
use Pin\Models\Model;

/**
 * 树结构工具
 */
class Tree
{
    /**
     * 校验树结构完整性
     *
     * @param  Collection<array-key, Model>  $models
     * @return list<array{id: int, rule: string, message: string}>
     */
    public function check(Collection $models): array
    {
        return app('pin.tree.checker')->check($models);
    }

    /**
     * 过滤节点并修剪空分支
     *
     * @param  Collection<array-key, Model>  $models
     * @param  callable(Model): bool  $predicate
     * @return Collection<int, Model>
     */
    public function filter(Collection $models, callable $predicate): Collection
    {
        return app('pin.tree.filter')->filter($models, $predicate);
    }

    /**
     * 按父节点、排序值和 ID 排序
     */
    public function sort(Collection $items): Collection
    {
        return app('pin.tree.sorter')->sort($items);
    }
}
