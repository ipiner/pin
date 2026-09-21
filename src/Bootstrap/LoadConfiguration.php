<?php

declare(strict_types=1);

namespace Pin\Bootstrap;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;
use Illuminate\Support\Str;
use Pin\Application;
use Pin\Support\Arr;

/**
 * Pin 配置加载器
 */
class LoadConfiguration extends BaseLoadConfiguration
{
    /**
     * 框架配置目录
     */
    protected const string CONFIG_PATH = __DIR__.'/../../config';

    /**
     * 合并环境配置
     */
    public static function loadedConfiguration(Application $app, ?string $env = null): void
    {
        /** @var Repository $repository */
        $repository = $app['config'];

        $env = $env ?: $repository->get('app.env');

        $overrides = is_file($file = static::CONFIG_PATH."/config.{$env}.php")
            ? require $file
            : [];

        if (is_file($file = $app->configPath("config.{$env}.php"))) {
            $overrides = static::mergeConfig($overrides, require $file);
        }

        foreach ($overrides as $name => $config) {
            $repository->set(
                $name,
                static::mergeConfig($repository->get($name) ?: [], $config),
            );
        }
    }

    /**
     * 加载配置
     *
     * @param  Application  $app
     */
    public function bootstrap(ApplicationContract $app): void
    {
        parent::bootstrap($app);

        $app['config']->set('app.env', $app['env']);

        if (! $app['config_loaded_from_cache']) {
            static::loadedConfiguration($app, $this->runningUnitTests() ? 'testing' : null);
        }

        $app['env'] = $app['config']->get('app.env');
        date_default_timezone_set($app['config']->get('app.timezone', 'UTC'));

        $app->loadedConfiguration();
    }

    /**
     * 递归合并配置
     */
    protected static function mergeConfig(array $defaults, array $overrides): array
    {
        return Arr::merge($defaults, $overrides);
    }

    /**
     * 加载应用配置
     */
    protected function loadApplicationConfigurationFiles(
        Application $app,
        Repository $repository,
    ): void {
        foreach ($this->getConfigurationFiles($app) as $key => $path) {
            $repository->set(
                $key,
                static::mergeConfig($repository->get($key) ?: [], require $path),
            );
        }
    }

    /**
     * 加载框架与应用配置
     *
     * @param  Application  $app
     */
    protected function loadConfigurationFiles(
        ApplicationContract $app,
        Repository $repository,
    ): void {
        $configPath = $app->configPath();

        try {
            $app->useConfigPath(static::CONFIG_PATH);
            parent::loadConfigurationFiles($app, $repository);
        } finally {
            $app->useConfigPath($configPath);
        }

        $this->loadApplicationConfigurationFiles($app, $repository);
    }

    /**
     * 判断是否运行单元测试
     */
    protected function runningUnitTests(?array $argv = null): bool
    {
        $argv ??= $_SERVER['argv'] ?? [];
        $executable = $argv[0] ?? '';

        if (! $executable) {
            return false;
        }

        return Str::endsWith($executable, ['phpunit', 'pest'])
            || (str_ends_with($executable, 'artisan') && ($argv[1] ?? null) === 'test')
            || str_contains($executable, 'paratest');
    }
}
