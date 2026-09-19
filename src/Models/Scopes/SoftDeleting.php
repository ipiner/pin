<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;

/**
 * 软删除查询作用域
 */
class SoftDeleting extends SoftDeletingScope
{
    /**
     * 默认排除已软删除记录
     */
    #[Override]
    public function apply(Builder $builder, Model $model): void
    {
        $this->withoutTrashed($builder, $model);
    }

    /**
     * 扩展 Builder 方法
     */
    #[Override]
    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(static function (Builder $builder) {
            return $builder->update($builder->getModel()->softDeletedValuesForUpdate(true));
        });
    }

    /**
     * 添加 onlyTrashed 宏方法
     */
    #[Override]
    protected function addOnlyTrashed(Builder $builder): void
    {
        $builder->macro(
            'onlyTrashed',
            fn (Builder $builder) => $this->onlyTrashed($builder->withoutGlobalScope($this))
        );
    }

    /**
     * 添加 restore 宏方法
     */
    #[Override]
    protected function addRestore(Builder $builder): void
    {
        $builder->macro('restore', static function (Builder $builder) {
            $builder->withTrashed();

            return $builder->update($builder->getModel()->softDeletedValuesForUpdate(false));
        });
    }

    /**
     * 添加 withoutTrashed 宏方法
     */
    #[Override]
    protected function addWithoutTrashed(Builder $builder): void
    {
        $builder->macro(
            'withoutTrashed',
            fn (Builder $builder) => $this->withoutTrashed($builder->withoutGlobalScope($this))
        );
    }

    /**
     * 仅查询软删除记录
     */
    protected function onlyTrashed(Builder $builder, ?Model $model = null): Builder
    {
        $model ??= $builder->getModel();
        $column = $model->getQualifiedDeletedAtColumn();
        $value = $model->softDeletedAtValue(false);

        return $value === null
            ? $builder->whereNotNull($column)
            : $builder->where($column, '!=', $value);
    }

    /**
     * 排除软删除记录
     */
    protected function withoutTrashed(Builder $builder, ?Model $model = null): Builder
    {
        $model ??= $builder->getModel();
        $column = $model->getQualifiedDeletedAtColumn();
        $value = $model->softDeletedAtValue(false);

        return $builder->where($column, $value);
    }
}
