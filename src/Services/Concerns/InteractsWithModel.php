<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Pin\Models\Model;
use Pin\Support\Traits\HasModel;

/**
 * 模型交互能力
 *
 * 提供 Service 与 Model 之间的基础桥接能力
 *
 * @template TModel of Model
 *
 * @use HasModel<TModel>
 */
trait InteractsWithModel
{
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
