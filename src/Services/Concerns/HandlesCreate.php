<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Closure;
use Pin\Models\Model;
use Pin\Services\Results\CreateResult;

/**
 * 创建操作
 *
 * 为 Service 提供统一的创建流程封装。
 *
 * @template TModel of Model
 */
trait HandlesCreate
{
    /**
     * 执行标准创建流程
     *
     * @param  (Closure(TModel,array):void)|null  $callback
     * @return CreateResult<TModel>
     */
    public function create(array $data, ?Closure $callback = null)
    {
        $model = $this->model()->transaction(function (Model $model) use ($data, $callback) {
            $this->saving(null, $data);
            $this->creating($data);

            /** @var TModel $item */
            $item = $model->create($data);

            $this->created($item, $data);
            $this->saved($item, $data);
            if ($callback) {
                $callback($item, $data);
            }

            return $item;
        });

        return new CreateResult($model);
    }

    /**
     * 创建前置操作
     */
    protected function creating(array &$data): void
    {
    }

    /**
     * 创建后置操作
     *
     * @param  TModel  $model
     */
    protected function created($model, array $data): void
    {
    }
}
