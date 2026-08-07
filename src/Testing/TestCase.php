<?php

declare(strict_types=1);

namespace Pin\Testing;

use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Override;
use Pin\Support\Invoker;

Pest::boot();

/**
 * Pin 测试基类
 *
 * 基于 Orchestra Testbench 构建，
 * 用于在测试环境中模拟 Laravel 应用实例，并加载自定义配置与服务。
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @param  class-string|object  $obj
     */
    protected function invoker(string|object $obj): Invoker
    {
        return new Invoker($obj);
    }

    /**
     * 替换测试环境配置加载器
     */
    #[Override]
    protected function overrideApplicationBindings($app): array
    {
        return [
            LoadConfiguration::class => \Pin\Bootstrap\LoadConfiguration::class,
        ];
    }

    /**
     * 获取测试环境加载的服务提供者
     *
     * @return array<int, class-string>
     */
    protected function providers(): array
    {
        return [];
    }

    /**
     * 创建加载 Pin 服务提供者的测试应用
     */
    #[Override]
    protected function resolveApplication()
    {
        return Application::configure(static::applicationBasePath())
            ->withProviders($this->providers())
            ->withCommands()
            ->create();
    }
}
