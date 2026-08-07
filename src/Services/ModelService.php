<?php

declare(strict_types=1);

namespace Pin\Services;

use Pin\Models\Model;

/**
 * 通用模型服务层（Service Layer），封装增删改查逻辑。
 *
 * @template TModel of Model
 *
 * @use Concerns\HandlesCreate<TModel>
 * @use Concerns\HandlesDelete<TModel>
 * @use Concerns\HandlesUpdate<TModel>
 * @use Concerns\InteractsWithModel<TModel>
 *
 * @extends Service<TModel>
 */
class ModelService extends Service
{
    use Concerns\HandlesCreate,
        Concerns\HandlesDelete,
        Concerns\HandlesQuery,
        Concerns\HandlesSave,
        Concerns\HandlesUpdate,
        Concerns\InteractsWithModel;
}
