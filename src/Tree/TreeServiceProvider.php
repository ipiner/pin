<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Contracts\Support\DeferrableProvider;
use Override;
use Pin\Support\ServiceProvider;

/**
 * 树结构数据服务提供者
 */
class TreeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * 注册树服务
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton('pin.tree', Tree::class);
        $this->app->singleton('pin.tree.checker', TreePathChecker::class);
        $this->app->singleton('pin.tree.filter', TreeFilter::class);
        $this->app->singleton('pin.tree.sorter', TreeSorter::class);
    }

    /**
     * 获取延迟加载的服务
     *
     * @return list<string>
     */
    #[Override]
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
