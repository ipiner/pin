<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\MigrationServiceProvider as BaseMigrationServiceProvider;
use Illuminate\Support\ServiceProvider;

/**
 * 数据库迁移服务提供者
 */
class MigrationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * 注册迁移创建器
     */
    public function register(): void
    {
        $this->app->register(BaseMigrationServiceProvider::class);
        $this->app->singleton('migration.creator', MigrationCreator::class);
    }

    /**
     * 获取延迟加载的服务
     */
    public function provides(): array
    {
        return ['migration.creator'];
    }
}
