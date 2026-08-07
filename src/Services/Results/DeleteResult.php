<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Pin\Models\Model;

/**
 * 删除结果
 *
 * @template TModel of Model
 */
class DeleteResult extends Result
{
    /**
     * 删除结果
     *
     * @param  TModel  $model  删除对应模型
     * @param  bool  $deleted  是否删除成功
     */
    public function __construct(public $model, public bool $deleted)
    {
    }

    /**
     * 转换为删除结果响应数据
     */
    public function toArray(): array
    {
        return [
            'deleted' => $this->deleted,
        ];
    }

    /**
     * 响应消息
     */
    public function message(): string
    {
        return __($this->deleted ? 'Delete successfully' : 'Delete failed');
    }
}
