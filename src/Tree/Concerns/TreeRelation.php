<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 树节点关系
 */
trait TreeRelation
{
    /**
     * 获取直接子节点
     *
     * @return HasMany<static, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'pid');
    }
}
