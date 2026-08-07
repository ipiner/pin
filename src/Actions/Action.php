<?php

declare(strict_types=1);

namespace Pin\Actions;

use Pin\Actions\Concerns\HasFake;
use Pin\Models\Model;
use Pin\Models\Queryable\Queryable;
use Pin\Support\Traits\HasContext;
use Pin\Support\Traits\HasModel;
use Pin\Support\Traits\HasValidation;

/**
 * 通用 Action 基类
 *
 * 提供：
 * - 模型操作
 * - 数据填充与验证
 * - 上下文共享
 * - Queryable 查询构建
 *
 * 适用于单一业务动作，如创建、更新、删除等。
 *
 * @template TModel of Model
 */
class Action
{
    use HasContext, HasFake, HasValidation;
    use HasModel {
        attributes as protected;
    }

    /**
     * 初始化 Action
     */
    public function boot(): void
    {
        $this->bootModel();
    }

    /**
     * 构建 Queryable 查询对象
     */
    public function queryable(): Queryable
    {
        return Queryable::fromRules($this->validationRules(), $this->payload);
    }
}
