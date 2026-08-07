<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

/**
 * 数据库迁移服务提供者
 */
class MigrationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton('migration.creator', MigrationCreator::class);
        $this->publishes(
            [__DIR__.'/../../database/migrations' => database_path('migrations')],
            'pin-migrations'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return ['migration.creator'];
    }
}
