<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;
use Pin\Models\Model;

/**
 * TreeFilter
 *
 * 树结构过滤器（Structure-aware Tree Filter）。
 */
class TreeFilter
{
    /**
     * 过滤树结构数据，并自动维护结构完整性。
     *
     * @param  Collection<Model>  $models
     * @param  callable(Model):bool  $predicate
     * @return Collection<Model>
     */
    public function filter(Collection $models, callable $predicate): Collection
    {
        $parentIds = $this->collectParentIds($models);
        $hiddenIds = $this->collectHiddenIds($models, $predicate);
        $filtered = $this->removeHiddenSubtrees($models, $hiddenIds);

        return $this->pruneEmptyParents($filtered, $parentIds);
    }

    /**
     * 收集所有需要隐藏的节点 ID。
     *
     * @param  Collection<Model>  $models
     * @param  callable(Model):bool  $predicate
     * @return array<int, bool>
     */
    protected function collectHiddenIds(Collection $models, callable $predicate): array
    {
        $hiddenIds = [];

        foreach ($models as $model) {
            if (! $predicate($model)) {
                $hiddenIds[$model->id] = true;
            }
        }

        return $hiddenIds;
    }

    /**
     * 收集原始树中“曾经拥有子节点”的父节点 ID。
     *
     * @param  Collection<Model>  $models
     * @return array<int, bool>
     */
    protected function collectParentIds(Collection $models): array
    {
        $parents = [];

        foreach ($models as $model) {
            $paths = $model->paths ?? [];
            $len = count($paths);
            if ($len > 1) {
                $pid = $paths[$len - 2];
                $parents[$pid] = true;
            }
        }

        return $parents;
    }

    /**
     * 修剪所有“空父节点”。
     *
     * @param  Collection<Model>  $models
     * @param  array<int, bool>  $parentIds
     * @return Collection<Model>
     */
    protected function pruneEmptyParents(Collection $models, array $parentIds): Collection
    {
        while (true) {
            $hasChildren = [];

            foreach ($models as $model) {
                $paths = $model->paths ?? [];
                $len = count($paths);

                if ($len > 1) {
                    $pid = $paths[$len - 2];
                    $hasChildren[$pid] = true;
                }
            }

            $removed = false;
            $models = $models->filter(function (Model $model) use (
                $parentIds,
                $hasChildren,
                &$removed
            ) {
                $id = $model->id;
                $isEmptyParent = isset($parentIds[$id]) && ! isset($hasChildren[$id]);

                if ($isEmptyParent) {
                    $removed = true;

                    return false;
                }

                return true;
            })
                ->values();

            // 结构稳定后结束循环
            if (! $removed) {
                break;
            }
        }

        return $models;
    }

    /**
     * 删除所有“属于隐藏节点子树”的数据。
     *
     * @param  Collection<Model>  $models
     * @param  array<int, bool>  $hiddenIds
     * @return Collection<Model>
     */
    protected function removeHiddenSubtrees(Collection $models, array $hiddenIds): Collection
    {
        return $models->filter(function (Model $model) use ($hiddenIds) {
            $paths = $model->paths ?? [];
            foreach ($paths as $id) {
                if (isset($hiddenIds[$id])) {
                    return false;
                }
            }

            return true;
        })
            ->values();
    }
}
