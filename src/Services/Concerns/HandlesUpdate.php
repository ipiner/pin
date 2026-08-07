<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Closure;
use Pin\Errors\Errors;
use Pin\Models\Model;
use Pin\Services\Results\UpdateResult;

/**
 * 更新操作
 *
 * 为 Service 提供统一更新流程封装
 *
 * @template TModel of Model
 */
trait HandlesUpdate
{
    /**
     * 执行标准更新流程
     *
     * @param  TModel|int  $model
     * @param  (Closure(TModel,array):void)|null  $callback
     * @return UpdateResult<TModel>
     */
    public function update($model, array $data, ?Closure $callback = null)
    {
        $model = $this->find($model);
        $updated = $model->transaction(function (Model $model) use ($data, $callback) {
            $this->saving($model, $data);
            $this->updating($model, $data);

            $updated = $model->update($data);
            if ($updated) {
                $this->updated($model, $data);
                $this->saved($model, $data);
                if ($callback) {
                    $callback($model, $data);
                }
            }

            return $updated;
        });

        return new UpdateResult($model, $updated);
    }

    /**
     * 更新前置操作
     *
     * @param  TModel  $model
     */
    protected function updating($model, array &$data): void
    {
        if (! isset($data['v'])) {
            return;
        }

        if ($model->v != $data['v']) {
            Errors::DataVersionMismatch->throw();
        }

        $data['v'] = $model->v + 1;
    }

    /**
     * 更新成功后置操作
     *
     * @param  TModel  $model
     */
    protected function updated($model, array $data): void
    {
    }
}
