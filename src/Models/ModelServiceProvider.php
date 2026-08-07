<?php

declare(strict_types=1);

namespace Pin\Models;

use Illuminate\Database\Eloquent\Builder;
use Pin\Models\Queryable\QueryableScope;
use Pin\Models\Scopes\Aggregate;
use Pin\Models\Scopes\Pagination;
use Pin\Models\Scopes\Sort;
use Pin\Support\ServiceProvider;

/**
 * 模型增强服务提供者
 *
 * 为 Eloquent Builder 注册分页、筛选、排序和聚合宏。
 */
class ModelServiceProvider extends ServiceProvider
{
    /**
     * 注册模型查询宏
     */
    public function boot(): void
    {
        Builder::mixin(new Aggregate());          // 聚合函数宏
        Builder::macro('pagination', Pagination::pagination()); // 分页宏
        Builder::macro('queryable', QueryableScope::queryable()); // 查询条件宏
        Builder::macro('sort', Sort::sort());     // 排序宏
    }
}
