<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;
use Pin\Models\Model;

/**
 * 树结构过滤器
 */
class TreeFilter
{
    /**
     * 过滤节点并修剪空分支
     *
     * @param  Collection<array-key, Model>  $models
     * @param  callable(Model): bool  $predicate
     * @return Collection<int, Model>
     */
    public function filter(Collection $models, callable $predicate): Collection
    {
        $parentIds = $this->collectParentIds($models);
        $hiddenIds = $this->collectHiddenIds($models, $predicate);
        $filtered = $this->removeHiddenSubtrees($models, $hiddenIds);

        return $this->pruneEmptyParents($filtered, $parentIds);
    }

    /**
     * 收集隐藏节点
     *
     * @param  Collection<array-key, Model>  $models
     * @param  callable(Model): bool  $predicate
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
     * 收集原始父节点
     *
     * @param  Collection<array-key, Model>  $models
     * @return array<int, bool>
     */
    protected function collectParentIds(Collection $models): array
    {
        $parents = [];

        foreach ($models as $model) {
            $paths = $model->paths ?? [];
            $depth = count($paths);

            if ($depth > 1) {
                $parents[$paths[$depth - 2]] = true;
            }
        }

        return $parents;
    }

    /**
     * 从叶端逐级修剪空父节点
     *
     * @param  Collection<array-key, Model>  $models
     * @param  array<int, bool>  $parentIds
     * @return Collection<int, Model>
     */
    protected function pruneEmptyParents(Collection $models, array $parentIds): Collection
    {
        $parents = [];
        $childCounts = [];

        foreach ($models as $model) {
            $paths = $model->paths ?? [];
            $depth = count($paths);

            if ($depth > 1) {
                $pid = $paths[$depth - 2];
                $parents[$model->id] = $pid;
                $childCounts[$pid] = ($childCounts[$pid] ?? 0) + 1;
            }
        }

        $pending = [];

        foreach ($models as $model) {
            if (isset($parentIds[$model->id]) && ! isset($childCounts[$model->id])) {
                $pending[] = $model->id;
            }
        }

        $removed = [];

        while ($pending) {
            $id = array_pop($pending);
            $removed[$id] = true;
            $pid = $parents[$id] ?? null;

            if ($pid !== null && --$childCounts[$pid] === 0 && isset($parentIds[$pid])) {
                $pending[] = $pid;
            }
        }

        return $models->reject(fn (Model $model) => isset($removed[$model->id]))->values();
    }

    /**
     * 移除隐藏节点及其后代
     *
     * @param  Collection<array-key, Model>  $models
     * @param  array<int, bool>  $hiddenIds
     * @return Collection<int, Model>
     */
    protected function removeHiddenSubtrees(Collection $models, array $hiddenIds): Collection
    {
        return $models->filter(function (Model $model) use ($hiddenIds) {
            if (isset($hiddenIds[$model->id])) {
                return false;
            }

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
