<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Pin\Providers\PinServiceProvider;

beforeEach(function () {
    $this->resourcePath = sys_get_temp_dir().'/pin-provider-'.uniqid();
    $this->publishes = PinServiceProvider::$publishes;
    $this->publishGroups = PinServiceProvider::$publishGroups;

    try {
        $this->providerApp = new class($this->resourcePath) extends Application
        {
            public bool $console = true;

            #[Override]
            public function runningInConsole(): bool
            {
                return $this->console;
            }
        };

        $this->providerApp->useConfigPath($this->resourcePath.'/custom-config');
        $this->providerApp->useLangPath($this->resourcePath.'/custom-lang');
        $this->providerApp->singleton('translator', static function (Application $app) {
            return new Translator(new FileLoader(new Filesystem(), $app->langPath()), 'zh_CN');
        });
        $this->provider = new PinServiceProvider($this->providerApp);
    } finally {
        Application::setInstance($this->app);
    }
});

afterEach(function () {
    PinServiceProvider::$publishes = $this->publishes;
    PinServiceProvider::$publishGroups = $this->publishGroups;
    $this->app['files']->deleteDirectory($this->resourcePath);
});

it('loads package translations', function () {
    $this->provider->boot();
    $translator = $this->providerApp->make('translator');

    expect($translator->get('Create successfully'))->toBe('新增成功')
        ->and($translator->get('pin::errors.success'))->toBe('请求成功');
});

it('loads published JSON translations', function (bool $resolved) {
    $path = $this->providerApp->langPath('vendor/pin');
    $this->app['files']->ensureDirectoryExists($path);
    $this->app['files']->put($path.'/zh_CN.json', '{"Create successfully":"Published"}');

    if ($resolved) {
        $this->providerApp->make('translator');
    }

    $this->provider->boot();
    $translator = $this->providerApp->make('translator');

    expect($translator->get('Create successfully'))->toBe('Published')
        ->and($translator->get('Update successfully'))->toBe('更新成功');
})->with([false, true]);

it('prefers application JSON translations', function () {
    $path = $this->providerApp->langPath();
    $this->app['files']->ensureDirectoryExists($path.'/vendor/pin');
    $this->app['files']->put($path.'/vendor/pin/zh_CN.json', '{"Create successfully":"Published"}');
    $this->app['files']->put($path.'/zh_CN.json', '{"Create successfully":"Application"}');

    $this->provider->boot();

    expect($this->providerApp->make('translator')->get('Create successfully'))->toBe('Application');
});

it('publishes resources to the provider application paths', function () {
    $this->provider->boot();

    expect(PinServiceProvider::pathsToPublish(PinServiceProvider::class, 'pin-config'))
        ->toContain($this->providerApp->configPath('pin'))
        ->and(PinServiceProvider::pathsToPublish(PinServiceProvider::class, 'pin-lang'))
        ->toContain($this->providerApp->langPath('vendor/pin'));
});

it('loads translations without registering publish paths for HTTP requests', function () {
    $this->providerApp->console = false;
    PinServiceProvider::$publishes = [];
    PinServiceProvider::$publishGroups = [];

    $this->provider->boot();

    expect(PinServiceProvider::pathsToPublish(PinServiceProvider::class))->toBe([])
        ->and($this->providerApp->make('translator')->get('Create successfully'))->toBe('新增成功');
});
