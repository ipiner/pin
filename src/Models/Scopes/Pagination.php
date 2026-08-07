<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pagination Scopes
 *
 * 提供 Eloquent 分页查询封装
 */
class Pagination
{
    /**
     * 分页方法封装
     */
    public static function pagination(): Closure
    {
        return function (?int $page = null, ?int $pageSize = null, array $columns = ['*']) {
            /** @var Builder $this */
            return \Pin\Pagination\Pagination::make($this->paginate(
                $pageSize,
                $columns,
                config('pin.pagination.page_name'),
                $page
            ));
        };
    }
}
