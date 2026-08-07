<?php

declare(strict_types=1);

namespace Pin\Bootstrap;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Pin\Support\Arr;

/**
 * 自定义配置加载器
 *
 * - 多层目录加载（框架 + 应用）
 * - 环境配置覆盖（config.{env}.php）
 * - 递归合并
 */
class LoadConfiguration extends \Illuminate\Foundation\Bootstrap\LoadConfiguration
{
    /**
     * 配置加载完成后，合并环境配置
     */
    public static function loadedConfiguration(\Pin\Application $app, ?string $env = null): void
    {
        /** @var Repository $repository */
        $repository = $app['config'];

        // 获取当前环境
        $env = $env ?: $repository->get('app.env');

        // 加载框架内置环境配置（优先级较低）
        $overrides = is_file($file = __DIR__."/../../config/config.{$env}.php")
            ? require $file
            : [];

        // 加载应用层环境配置（优先级更高）
        if (is_file($file = $app->configPath("config.{$env}.php"))) {
            $overrides = static::mergeConfig($overrides, require $file);
        }

        // 合并到现有配置（递归 merge，而非覆盖）
        foreach ($overrides as $name => $config) {
            $repository->set(
                $name,
                static::mergeConfig($repository->get($name) ?: [], $config)
            );
        }
    }

    /**
     * 启动配置加载流程
     */
    public function bootstrap(Application $app): void
    {
        // Laravel 默认配置加载
        parent::bootstrap($app);

        /** @var \Pin\Application $app */

        // 同步 env（避免 config 与 app 不一致）
        $app['config']->set('app.env', $app['env']);

        // 加载环境覆盖配置（测试环境特殊处理）
        $this->loadedConfiguration(
            $app,
            $this->runningUnitTests() ? 'testing' : null
        );

        // 回写 env（确保最终一致）
        $app['env'] = $app['config']->get('app.env');

        // 配置加载完成
        $app->loadedConfiguration();
    }

    /**
     * 递归合并配置
     *
     * @param  array  $defaults  原配置
     * @param  array  $overrides  覆盖配置
     */
    protected static function mergeConfig(array $defaults, array $overrides): array
    {
        return Arr::merge($defaults, $overrides);
    }

    /**
     * 加载应用层配置文件
     */
    protected function loadApplicationConfigurationFiles(
        \Pin\Application $app,
        Repository $repository
    ): void {
        foreach ($this->getConfigurationFiles($app) as $key => $path) {
            $repository->set(
                $key,
                $this->mergeConfig($repository->get($key) ?: [], require $path)
            );
        }
    }

    /**
     * 重写配置加载逻辑
     *
     * 实现“双配置目录”加载：
     *
     * 1. 先加载框架配置（pin/config）
     * 2. 再加载应用配置（项目 config）
     */
    protected function loadConfigurationFiles(Application $app, Repository $repository)
    {
        // 保存原始 config 目录
        $configPath = $app->configPath();

        // 切换到框架配置目录
        $app->useConfigPath(__DIR__.'/../../config');

        // 加载框架默认配置
        parent::loadConfigurationFiles($app, $repository);

        // 切回应用配置目录
        $app->useConfigPath($configPath);

        /** @var \Pin\Application $app */

        // 加载应用配置（覆盖框架配置）
        $this->loadApplicationConfigurationFiles($app, $repository);
    }

    /**
     * 判断是否运行在单元测试环境
     *
     * 识别方式：
     * - phpunit
     * - php artisan test
     * - pest
     * - paratest（并行测试）
     */
    protected function runningUnitTests(?array $argv = null): bool
    {
        $argv = $argv ?? ($_SERVER['argv'] ?? null);

        if (! $argv) {
            return false;
        }

        return Str::endsWith($argv[0], ['phpunit', 'pest'])
            || str_ends_with($argv[0], 'artisan') && 'test' === ($argv[1] ?? null)
            || str_contains($argv[0], 'paratest');
    }
}
