<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Closure;
use Pin\Errors\Errors;
use Pin\Models\Model;
use Pin\Services\Results\CreateResult;

/**
 * 创建操作
 *
 * @template TModel of Model
 */
trait HandlesCreate
{
    /**
     * 创建模型
     *
     * @param  array<string, mixed>  $data
     * @param  (Closure(TModel, array): void)|null  $callback
     * @return CreateResult<TModel>
     */
    public function create(array $data, ?Closure $callback = null): CreateResult
    {
        $model = $this->model()->transaction(function (Model $model) use ($data, $callback) {
            $this->saving(null, $data);
            $this->creating($data);

            /** @var TModel $model */
            $model = $model->create($data);
            if (! $model->exists) {
                Errors::CreateFailed->throw();
            }

            $this->created($model, $data);
            $this->saved($model, $data);
            if ($callback) {
                $callback($model, $data);
            }

            return $model;
        });

        return new CreateResult($model);
    }

    /**
     * 创建前处理
     */
    protected function creating(array &$data): void
    {
    }

    /**
     * 创建后处理
     *
     * @param  TModel  $model
     */
    protected function created($model, array $data): void
    {
    }
}
