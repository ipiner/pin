<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Pin\Models\Model;
use Pin\Support\Traits\HasModel;

/**
 * 模型查找
 *
 * @template TModel of Model
 */
trait InteractsWithModel
{
    /** @use HasModel<TModel> */
    use HasModel;

    /**
     * 查找模型
     *
     * @param  TModel|int  $model
     * @return TModel
     */
    protected function find($model)
    {
        return $model instanceof $this->modelClass
            ? $model
            : $this->modelClass::findOrFail($model);
    }
}
