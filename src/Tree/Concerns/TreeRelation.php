<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TreeRelation
 *
 * 树形结构的关系定义层，负责定义模型之间的“父子关系（Parent-Child Relationship）”。
 */
trait TreeRelation
{
    /**
     * 获取当前节点的直接子节点（Direct Children）
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'pid');
    }
}
