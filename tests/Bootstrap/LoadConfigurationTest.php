<?php

declare(strict_types=1);

use Pin\Bootstrap\LoadConfiguration;

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
