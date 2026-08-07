<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Closure;
use Pin\Models\Model;
use Pin\Services\Results\DeleteResult;

/**
 * 删除操作
 *
 * 为 Service 提供统一删除流程封装
 *
 * @template TModel of Model
 */
trait HandlesDelete
{
    /**
     * 执行标准删除流程
     *
     * @param  TModel|int  $model
     * @param  (Closure(TModel,array):void)|null  $callback
     * @return DeleteResult<TModel>
     */
    public function delete($model, ?Closure $callback = null)
    {
        $model = $this->find($model);
        $deleted = $model->transaction(function (Model $model) use ($callback) {
            $this->deleting($model);

            $deleted = $model->delete();
            if ($deleted) {
                $this->deleted($model);
                if ($callback) {
                    $callback($model);
                }
            }

            return $deleted;
        });

        return new DeleteResult($model, $deleted);
    }

    /**
     * 删除前置操作
     *
     * @param  TModel  $model
     */
    protected function deleting($model): void
    {
    }

    /**
     * 删除成功后置操作
     *
     * @param  TModel  $model
     */
    protected function deleted($model): void
    {
    }
}
