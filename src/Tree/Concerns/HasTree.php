<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use WeakMap;

/**
 * HasTree
 *
 * Tree 模型能力的“组合入口层（Facade Trait）”，
 * 将树结构的所有能力模块统一挂载到 Eloquent Model 上。
 *
 * @property int $pid
 * @property int $level
 * @property int $sort
 * @property string $name
 * @property string $path
 * @property int[] $paths
 */
trait HasTree
{
    protected static WeakMap $treeSnapshots;

    use TreeIdGenerator,
        TreeLevel,
        TreeNavigation,
        TreePath,
        TreePresenter,
        TreeQuery,
        TreeRelation;

    /**
     * 自动补齐树节点 id、path、level 和排序值
     */
    public static function bootHasTree(): void
    {
        static::$treeSnapshots ??= new WeakMap();

        static::creating(function (self $item) {
            $item->id = $item->id ?: $item->generateNodeId();
            $item->pid = (int) $item->pid;
            $item->path = static::buildPath($item->id, $item->pid);
            $item->level = $item->pathLevel();
            $item->ensureLevelValid();
            $item->sort = blank($item->sort) || $item->sort === -1
                ? $item->id
                : $item->sort;
        });

        static::updating(function (self $item) {
            if ($item->isDirty('pid')) {
                $item->pid = (int) $item->pid;
                static::$treeSnapshots[$item] = $item->getRawOriginal();
                $item->path = static::buildPath($item->id, $item->pid);
                $item->level = $item->pathLevel();
                $item->ensureLevelValid();
            }

            if ($item->sort === -1) {
                $item->sort = $item->id;
            }
        });

        static::updated(function (self $item) {
            if (! $item->wasChanged('pid')) {
                return;
            }

            $original = static::$treeSnapshots[$item];
            $item::relocateSubtree($original['path'], $item->path);
            unset(static::$treeSnapshots[$item]);

        });
    }
}
