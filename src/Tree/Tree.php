<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;
use Pin\Models\Model;

class Tree
{
    /**
     * 校验树结构完整性。
     *
     * @param  Collection<Model>  $models
     * @return array<int, array{
     *     id:int,
     *     rule:string,
     *     message:string
     * }>
     */
    public function check(Collection $models): array
    {
        return app('pin.tree.checker')->check($models);
    }

    /**
     * 过滤树结构数据，并自动维护结构完整性。
     *
     * @param  Collection<Model>  $models
     * @param  callable(Model):bool  $predicate
     * @return Collection<Model>
     */
    public function filter(Collection $models, callable $predicate): Collection
    {
        return app('pin.tree.filter')->filter($models, $predicate);
    }

    /**
     * 排序树节点集合
     */
    public function sort(Collection $items): Collection
    {
        return app('pin.tree.sorter')->sort($items);
    }
}
