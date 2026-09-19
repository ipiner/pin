<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Models\Model;

/**
 * 可观察事件
 */
const ObservableEvents = [
    'retrieved', 'creating', 'created', 'updating', 'updated',
    'saving', 'saved', 'restoring', 'restored', 'replicating',
    'trashed', 'deleting', 'deleted', 'forceDeleting', 'forceDeleted',
];

/**
 * 为模型自动绑定 on{Event} 事件处理方法
 */
trait HasEvents
{
    /**
     * 启动模型事件自动绑定
     */
    public static function bootHasEvents(): void
    {
        foreach (ObservableEvents as $event) {
            $method = 'on'.ucfirst($event);

            if (! method_exists(static::class, $method)) {
                continue;
            }

            static::registerModelEvent($event, static fn (Model $model) => $model->{$method}());
        }
    }

    /**
     * 更新前事件占位
     */
    protected function onUpdating()
    {
    }

    /**
     * 创建后事件占位
     */
    protected function onCreated()
    {
    }

    /**
     * 创建前事件占位
     */
    protected function onCreating()
    {
    }

    /**
     * 删除后事件占位
     */
    protected function onDeleted()
    {
    }

    /**
     * 删除前事件占位
     */
    protected function onDeleting()
    {
    }

    /**
     * 强制删除后事件占位
     */
    protected function onForceDeleted()
    {
    }

    /**
     * 强制删除前事件占位
     */
    protected function onForceDeleting()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    protected function onReplicating()
    {
    }

    /**
     * 检索后事件占位
     */
    protected function onRetrieved()
    {
    }

    /**
     * 保存后事件占位
     */
    protected function onSaved()
    {
    }

    /**
     * 保存前事件占位
     */
    protected function onSaving()
    {
    }

    /**
     * 更新后事件占位
     */
    protected function onUpdated()
    {
    }
}
