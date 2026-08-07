<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Pin\Models\Model;

/**
 * 创建结果
 *
 * @template TModel of Model
 */
class CreateResult extends Result
{
    /**
     * 创建后的模型实例
     *
     * @param  TModel  $model
     */
    public function __construct(public $model)
    {
    }

    /**
     * 转换为创建结果响应数据
     */
    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
        ];
    }

    /**
     * 响应消息
     */
    public function message(): string
    {
        return __('Create successfully');
    }
}
