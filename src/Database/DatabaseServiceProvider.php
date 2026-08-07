<?php

/**
 * 数据库服务提供者
 */

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Database\Events\QueryExecuted as EventQueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

/**
 * 数据库服务提供者
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton(QueryMonitor::class);

        DB::listen(function (EventQueryExecuted $event) {
            app(QueryMonitor::class)->handle($event);
        });
        app()->terminating(fn () => app(QueryMonitor::class)->logger->flush());
    }
}
