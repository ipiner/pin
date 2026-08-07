<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Pin\Models\Model;

/**
 * 更新结果
 *
 * @template TModel of Model
 */
class UpdateResult extends Result
{
    /**
     * 更新结果
     *
     * @param  TModel  $model  更新后的模型
     * @param  bool  $updated  是否更新成功
     */
    public function __construct(public $model, public bool $updated)
    {
    }

    /**
     * @return array{updated: bool, v: int|null}
     */
    public function toArray(): array
    {
        return array_filter([
            'updated' => $this->updated,
            'v' => $this->model->v,
        ], fn ($value) => $value !== null);
    }

    /**
     * 响应消息
     */
    public function message(): string
    {
        return __($this->updated ? 'Update successfully' : 'Update failed');
    }
}
