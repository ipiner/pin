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
 * 模型服务提供者
 */
class ModelServiceProvider extends ServiceProvider
{
    /**
     * 注册模型查询宏
     */
    public function boot(): void
    {
        Builder::mixin(new Aggregate());
        Builder::macro('pagination', Pagination::pagination());
        Builder::macro('queryable', QueryableScope::queryable());
        Builder::macro('sort', Sort::sort());
    }
}
