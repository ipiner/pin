<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Override;
use Pin\Models\Model;

/**
 * 更新结果
 *
 * @template TModel of Model
 */
class UpdateResult extends Result
{
    /**
     * @param  TModel  $model
     */
    public function __construct(public Model $model, public bool $updated)
    {
    }

    /**
     * 更新结果数据
     *
     * @return array{updated: bool, v?: int}
     */
    #[Override]
    public function toArray(): array
    {
        $data = ['updated' => $this->updated];
        $version = $this->model->v;

        if ($version !== null) {
            $data['v'] = $version;
        }

        return $data;
    }

    /**
     * 响应消息
     */
    #[Override]
    public function message(): string
    {
        return __($this->updated ? 'Update successfully' : 'Update failed');
    }
}
