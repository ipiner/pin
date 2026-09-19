<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Override;
use Pin\Models\Model;

/**
 * 创建结果
 *
 * @template TModel of Model
 */
class CreateResult extends Result
{
    /**
     * @param  TModel  $model
     */
    public function __construct(public Model $model)
    {
    }

    /**
     * 创建结果数据
     *
     * @return array{id: int|null}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
        ];
    }

    /**
     * 响应消息
     */
    #[Override]
    public function message(): string
    {
        return __('Create successfully');
    }
}
