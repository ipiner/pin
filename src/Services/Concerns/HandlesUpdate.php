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
 * @template TModel of Model
 */
trait HandlesUpdate
{
    /**
     * 更新模型
     *
     * @param  TModel|int  $model
     * @param  array<string, mixed>  $data
     * @param  (Closure(TModel, array): void)|null  $callback
     * @return UpdateResult<TModel>
     */
    public function update($model, array $data, ?Closure $callback = null): UpdateResult
    {
        $model = $this->find($model);
        $updated = $model->transaction(function (Model $model) use ($data, $callback): bool {
            $this->saving($model, $data);
            $this->updating($model, $data);

            if (! $model->update($data)) {
                return false;
            }

            $this->updated($model, $data);
            $this->saved($model, $data);
            if ($callback) {
                $callback($model, $data);
            }

            return true;
        });

        return new UpdateResult($model, $updated);
    }

    /**
     * 更新前处理
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
     * 更新后处理
     *
     * @param  TModel  $model
     */
    protected function updated($model, array $data): void
    {
    }
}
