<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;

/**
 * SoftDeleting Scope
 *
 * 提供 Eloquent 模型的软删除全局作用域功能
 */
class SoftDeleting extends SoftDeletingScope
{
    /**
     * 默认排除已软删除记录
     */
    #[Override]
    public function apply(Builder $builder, Model $model)
    {
        $this->withoutTrashed($builder, $model);
    }

    /**
     * 扩展 Builder 方法
     */
    #[Override]
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(
            fn (Builder $builder) => $builder->update($builder->getModel()->softDeletedValuesForUpdate(true))
        );
    }

    /**
     * 添加 onlyTrashed 宏方法
     */
    #[Override]
    protected function addOnlyTrashed(Builder $builder)
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
    protected function addRestore(Builder $builder)
    {
        $builder->macro('restore', function (Builder $builder) {
            $builder->withTrashed();

            // 恢复软删除，将 deleted_at 值重置
            return $builder->update($builder->getModel()->getModel()->softDeletedValuesForUpdate(false));
        });
    }

    /**
     * 添加 withoutTrashed 宏方法
     */
    #[Override]
    protected function addWithoutTrashed(Builder $builder)
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
        $model = $model ?: $builder->getModel();
        $column = $model->getQualifiedDeletedAtColumn();
        $value = $model->softDeletedAtValue(false);

        return $value === null ? $builder->whereNotNull($column) : $builder->where($column, '>', 0);
    }

    /**
     * 排除软删除记录
     */
    protected function withoutTrashed(Builder $builder, ?Model $model = null): Builder
    {
        $model = $model ?: $builder->getModel();
        $column = $model->getQualifiedDeletedAtColumn();
        $value = $model->softDeletedAtValue(false);

        return match ($value) {
            null => $builder->whereNull($column),
            default => $builder->where($column, 0),
        };
    }
}
