<?php

declare(strict_types=1);

use Pin\Bootstrap\LoadConfiguration;

it('restores the application config path when framework configuration fails', function () {
    $path = $this->app->configPath();
    $loader = Mockery::mock(LoadConfiguration::class)->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $loader->shouldReceive('getBaseConfiguration')->once()
        ->andThrow(new RuntimeException('Configuration failed.'));

    try {
        expect(fn () => $this->invoker($loader)->loadConfigurationFiles(
            $this->app,
            $this->app['config'],
        ))->toThrow(RuntimeException::class, 'Configuration failed.');

        expect($this->app->configPath())->toBe($path);
    } finally {
        $this->app->useConfigPath($path);
    }
});

it('uses cached configuration without reapplying environment overrides', function () {
    $items = $this->app['config']->all();
    $items['app']['app_env'] = 'cached';
    LoadConfiguration::alwaysUse(fn () => $items);

    try {
        (new LoadConfiguration())->bootstrap($this->app);

        expect($this->app['config_loaded_from_cache'])->toBeTrue()
            ->and($this->app['config']->get('app.app_env'))->toBe('cached');
    } finally {
        LoadConfiguration::alwaysUse(null);
    }
});

it('applies the timezone from environment configuration', function () {
    $path = $this->app->configPath();
    $timezone = date_default_timezone_get();
    $directory = sys_get_temp_dir().'/pin-bootstrap-'.uniqid();
    mkdir($directory);
    file_put_contents(
        $directory.'/config.testing.php',
        '<?php return '.var_export(['app' => ['timezone' => 'Asia/Tokyo']], true).';',
    );

    try {
        $this->app->useConfigPath($directory);
        (new LoadConfiguration())->bootstrap($this->app);

        expect($this->app['config']->get('app.timezone'))->toBe('Asia/Tokyo')
            ->and(date_default_timezone_get())->toBe('Asia/Tokyo');
    } finally {
        $this->app->useConfigPath($path);
        date_default_timezone_set($timezone);
        unlink($directory.'/config.testing.php');
        rmdir($directory);
    }
});

it('loads environment configuration', function () {
    $this->copyConfigFile('config.production.php');

    $configuration = new LoadConfiguration();

    // 默认 bootstrap
    $configuration->bootstrap($this->app);
    expect(config('app.app_env'))->toBe('config.testing');

    // 手动加载 production 配置
    $this->invoker($configuration)->loadedConfiguration($this->app, 'production');
    expect(config('app.app_env'))->toBe('config.production');
});

it('merges configuration arrays recursively', function () {
    $defaults = [
        'debug' => false,
        'array' => [
            'name' => 'foo',
        ],
    ];

    $config = [
        'debug' => true,
        'array' => [
            'email' => 'foo@example.com',
        ],
    ];

    $result = $this->invoker(LoadConfiguration::class)
        ->mergeConfig($defaults, $config);

    expect($result['debug'])->toBeTrue()
        ->and($result['array'])
        ->toBe([
            'name' => 'foo',
            'email' => 'foo@example.com',
        ]);
});

it('detects unit test environment', function (?array $argv, bool $expected) {
    $invoker = $this->invoker(LoadConfiguration::class);

    expect($invoker->runningUnitTests($argv))
        ->toBe($expected);
})->with([
    'default argv' => [null, true],
    'empty argv' => [[], false],
    '/vendor/pest' => [['/vendor/pest'], true],
    '/vendor/phpunit' => [['/vendor/phpunit'], true],
    '/bin/artisan' => [['/bin/artisan'], false],
    '/bin/artisan tests' => [['/bin/artisan', 'tests'], false],
    '/bin/artisan test' => [['/bin/artisan', 'test'], true],
    '/vendor/brianium/paratest/bin/paratest' => [['/vendor/brianium/paratest/bin/paratest'], true],
]);
