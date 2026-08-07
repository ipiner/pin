<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Models\Model;
use Throwable;

/**
 * 可观察事件
 *
 * 为 Eloquent 模型提供事件绑定功能，将模型事件（retrieved, creating, created 等）
 * 自动映射到模型实例上的对应方法（on{Event}）。
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
            if (method_exists(static::class, $event)) {
                try {
                    /** @phpstan-ignore-next-line */
                    static::$event(function (Model $item) use ($event) {
                        return method_exists($item, $method = 'on'.$event) ? $item->{$method}() : true;
                    });
                } catch (Throwable) {
                }
            }
        }
    }

    // 事件处理占位方法
    //
    // 模型中可以覆盖这些方法实现自定义逻辑

    /**
     * 更新前事件占位
     */
    protected function OnUpdating()
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
