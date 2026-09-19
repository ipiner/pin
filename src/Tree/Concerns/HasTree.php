<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use WeakMap;

/**
 * Pin 模型的树结构能力。
 *
 * @property int $pid
 * @property int $level
 * @property int $sort
 * @property string $name
 * @property string $path
 * @property list<int> $paths
 */
trait HasTree
{
    use TreeIdGenerator,
        TreeLevel,
        TreeNavigation,
        TreePath,
        TreePresenter,
        TreeQuery,
        TreeRelation;

    /**
     * 移动前的路径。
     *
     * @var WeakMap<static, string>
     */
    protected static WeakMap $treeSnapshots;

    /**
     * 注册节点初始化和移动事件。
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
                $item->ensureParentValid();
                $item->path = static::buildPath($item->id, $item->pid);
                $item->level = $item->pathLevel();
                $item->ensureLevelValid();
                static::$treeSnapshots[$item] = $item->getRawOriginal('path');
            }

            if ($item->sort === -1) {
                $item->sort = $item->id;
            }
        });

        static::updated(function (self $item) {
            if (! $item->wasChanged('pid')) {
                return;
            }

            try {
                $item::relocateSubtree(static::$treeSnapshots[$item], $item->path);
            } finally {
                unset(static::$treeSnapshots[$item]);
            }
        });
    }
}
