<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Closure;
use Pin\Models\Model;
use Pin\Services\Results\DeleteResult;

/**
 * 删除操作
 *
 * @template TModel of Model
 */
trait HandlesDelete
{
    /**
     * 删除模型
     *
     * @param  TModel|int  $model
     * @param  (Closure(TModel): void)|null  $callback
     * @return DeleteResult<TModel>
     */
    public function delete($model, ?Closure $callback = null): DeleteResult
    {
        $model = $this->find($model);
        $deleted = $model->transaction(function (Model $model) use ($callback): bool {
            $this->deleting($model);

            if (! $model->delete()) {
                return false;
            }

            $this->deleted($model);
            if ($callback) {
                $callback($model);
            }

            return true;
        });

        return new DeleteResult($model, $deleted);
    }

    /**
     * 删除前处理
     *
     * @param  TModel  $model
     */
    protected function deleting($model): void
    {
    }

    /**
     * 删除后处理
     *
     * @param  TModel  $model
     */
    protected function deleted($model): void
    {
    }
}
