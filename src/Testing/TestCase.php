<?php

declare(strict_types=1);

namespace Pin\Testing;

use Illuminate\Foundation\Application as BaseApplication;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;
use Pin\Bootstrap\LoadConfiguration;
use Pin\Support\Invoker;

Pest::boot();

/**
 * Pin 测试基类
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param  class-string|object  $obj
     */
    protected function invoker(string|object $obj): Invoker
    {
        return new Invoker($obj);
    }

    /**
     * 替换配置加载器
     *
     * @param  BaseApplication  $app
     * @return array<class-string, class-string>
     */
    #[Override]
    protected function overrideApplicationBindings($app): array
    {
        return [
            BaseLoadConfiguration::class => LoadConfiguration::class,
        ];
    }

    /**
     * 获取附加服务提供者
     *
     * @return list<class-string<ServiceProvider>>
     */
    protected function providers(): array
    {
        return [];
    }

    /**
     * 创建测试应用
     */
    #[Override]
    protected function resolveApplication(): BaseApplication
    {
        return Application::configure(static::applicationBasePath())
            ->withProviders($this->providers())
            ->withCommands()
            ->create();
    }
}
