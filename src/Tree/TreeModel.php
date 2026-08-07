<?php

declare(strict_types=1);

namespace Pin\Tree;

use Pin\Models\Concerns\RedisId;
use Pin\Models\Concerns\SoftDeletes;
use Pin\Models\Model;
use Pin\Tree\Concerns\HasTree;

/**
 * 树结构基础模型
 */
class TreeModel extends Model
{
    use HasTree, RedisId, SoftDeletes;
}
