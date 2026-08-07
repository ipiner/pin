<?php

declare(strict_types=1);

namespace Pin\Support;

/**
 * ServiceProvider
 *
 * 扩展版服务提供者基类
 *
 * 对 Laravel 原生 ServiceProvider 进行增强：
 *
 * - 重写 mergeConfigFrom 方法
 * - 支持“递归合并配置”（而不是简单覆盖）
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * 合并配置文件
     *
     * @param  string  $path  配置文件路径
     * @param  string|null  $key  配置键名（默认使用文件名）
     */
    protected function mergeConfigFrom($path, $key): void
    {
        // 如果配置已缓存，则跳过（Laravel 标准行为）
        if ($this->app->configurationIsCached()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        // 默认使用文件名作为 key（如 config/xxx.php -> xxx）
        $key = $key ?: basename($path, '.php');

        $config = $this->app->make('config');

        // 递归合并配置：
        // - 默认配置在前
        // - 用户配置在后（优先级更高）
        $config->set(
            $key,
            Arr::merge(
                require $path,
                $config->get($key, [])
            )
        );
    }
}
