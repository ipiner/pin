<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Pin\Models\Model;
use Pin\Models\Queryable\Queryable;
use Pin\Pagination\Pagination;

/**
 * 查询操作
 *
 * 为 Service 提供统一的查询封装。
 *
 * @template TModel of Model
 */
trait HandlesQuery
{
    /**
     * 查询条件对象
     */
    protected ?Queryable $queryable = null;

    /**
     * 执行分页查询
     *
     * @param  Queryable|array|null  $rules  `Queryable` 可查询对象 或者 验证规则
     */
    public function pagination(Queryable|array|null $rules = null): Pagination
    {
        if ($rules) {
            $this->queryable = is_array($rules) ? Queryable::fromRules($rules) : $rules;
        }

        if ($this->context('paging') !== false) {
            return $this->queryBuilder()->pagination();
        }

        $items = $this->getAll();
        $total = $items->count();

        return Pagination::make(new LengthAwarePaginator(
            $items,
            $total,
            $total ?: 1,
        ));
    }

    /**
     * 查询数据
     */
    protected function getAll(): Collection
    {
        return $this->queryable?->conditions
            ? $this->queryBuilder()->get()
            : $this->modelClass::findAll()->values();
    }

    /**
     * 创建查询构建器
     *
     * @return Builder<TModel>
     */
    protected function queryBuilder(): Builder
    {
        return $this->modelClass::orderByDesc('id')->queryable($this->queryable);
    }
}
