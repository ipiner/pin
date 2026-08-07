<?php

declare(strict_types=1);

namespace Pin\Services;

use Pin\Models\Model;
use Pin\Support\Traits\HasContext;
use Pin\Support\Traits\HasModel;

/**
 * 基础服务类
 *
 * @template TModel of Model
 *
 * @use HasModel<TModel>
 */
class Service
{
    use HasContext, HasModel;

    /**
     * @param  class-string<TModel>  $model  模型类
     */
    public function __construct(?string $model = null)
    {
        $this->context([]);
        $this->bootModel($model);
    }
}
