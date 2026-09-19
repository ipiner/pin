<?php

declare(strict_types=1);

namespace Pin\Services;

use Pin\Models\Model;

/**
 * 模型增删改查服务
 *
 * @template TModel of Model
 *
 * @extends Service<TModel>
 */
class ModelService extends Service
{
    /** @use Concerns\HandlesCreate<TModel> */
    use Concerns\HandlesCreate;

    /** @use Concerns\HandlesDelete<TModel> */
    use Concerns\HandlesDelete;

    /** @use Concerns\HandlesQuery<TModel> */
    use Concerns\HandlesQuery;

    /** @use Concerns\HandlesSave<TModel> */
    use Concerns\HandlesSave;

    /** @use Concerns\HandlesUpdate<TModel> */
    use Concerns\HandlesUpdate;

    /** @use Concerns\InteractsWithModel<TModel> */
    use Concerns\InteractsWithModel;
}
