<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Support\Facades\Actor;

/**
 * 操作用户追踪
 */
trait HasBlameable
{
    /**
     * 创建用户字段名
     */
    protected const string CREATED_BY = 'created_by';

    /**
     * 更新用户字段名
     */
    protected const string UPDATED_BY = 'updated_by';

    /**
     * 注册模型事件
     */
    public static function bootHasBlameable(): void
    {
        static::creating(function ($model) {
            $model->{static::CREATED_BY} = Actor::id();
            $model->{static::UPDATED_BY} = Actor::id();
        });

        static::updating(function ($model) {
            $model->{static::UPDATED_BY} = Actor::id();
        });
    }
}
