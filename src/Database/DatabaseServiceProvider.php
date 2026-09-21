<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobAttempted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

/**
 * 数据库服务提供者
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * 注册查询监控
     */
    public function register(): void
    {
        $this->app->scoped(QueryMonitor::class);
    }

    /**
     * 监听查询事件
     */
    public function boot(): void
    {
        DB::listen(static function (QueryExecuted $event) {
            app(QueryMonitor::class)->handle($event);
        });

        $flush = static fn () => app(QueryMonitor::class)->logger->flush();

        $this->app['events']->listen(JobAttempted::class, $flush);
        $this->app->terminating($flush);
    }
}
