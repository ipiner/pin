<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Override;
use Pin\Models\Model;

/**
 * 删除结果
 *
 * @template TModel of Model
 */
class DeleteResult extends Result
{
    /**
     * @param  TModel  $model
     */
    public function __construct(public Model $model, public bool $deleted)
    {
    }

    /**
     * 删除结果数据
     *
     * @return array{deleted: bool}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'deleted' => $this->deleted,
        ];
    }

    /**
     * 响应消息
     */
    #[Override]
    public function message(): string
    {
        return __($this->deleted ? 'Delete successfully' : 'Delete failed');
    }
}
