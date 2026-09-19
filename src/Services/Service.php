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
 */
class Service
{
    use HasContext;

    /** @use HasModel<TModel> */
    use HasModel;

    /**
     * @param  class-string<TModel>|null  $model
     */
    public function __construct(?string $model = null)
    {
        $this->bootModel($model);
    }
}
