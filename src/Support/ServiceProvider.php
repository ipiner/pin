<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Override;

/**
 * 支持递归合并配置的服务提供者。
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * 合并配置文件
     *
     * @param  string  $path  配置文件路径
     * @param  string|null  $key  配置键名（默认使用文件名）
     */
    #[Override]
    protected function mergeConfigFrom($path, $key): void
    {
        if ($this->app->configurationIsCached()) {
            return;
        }

        $key = $key ?: basename($path, '.php');

        $config = $this->app->make('config');

        $config->set(
            $key,
            Arr::merge(require $path, $config->get($key, []))
        );
    }
}
