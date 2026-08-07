<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Contracts\Support\DeferrableProvider;
use Pin\Support\ServiceProvider;

/**
 * 树结构数据服务提供者。
 */
class TreeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton('pin.tree', Tree::class);
        $this->app->singleton('pin.tree.checker', TreePathChecker::class);
        $this->app->singleton('pin.tree.filter', TreeFilter::class);
        $this->app->singleton('pin.tree.sorter', TreeSorter::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'pin.tree',
            'pin.tree.checker',
            'pin.tree.filter',
            'pin.tree.sorter',
        ];
    }
}
