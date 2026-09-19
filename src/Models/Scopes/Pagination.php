<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Pin\Pagination\Pagination as PaginationResult;

/**
 * 分页查询宏
 */
class Pagination
{
    /**
     * 创建分页宏
     */
    public static function pagination(): Closure
    {
        return function (?int $page = null, ?int $pageSize = null, array $columns = ['*']) {
            /** @var Builder $this */
            return PaginationResult::make($this->paginate(
                $pageSize,
                $columns,
                config('pin.pagination.page_name'),
                $page
            ));
        };
    }
}
