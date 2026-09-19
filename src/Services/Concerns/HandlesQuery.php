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
     * @param  Queryable|array<string, mixed>|null  $rules  查询条件或验证规则
     * @return Pagination<TModel>
     */
    public function pagination(Queryable|array|null $rules = null): Pagination
    {
        if ($rules !== null) {
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
     * 查询全部数据
     *
     * @return Collection<int, TModel>
     */
    protected function getAll(): Collection
    {
        return $this->queryable
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
