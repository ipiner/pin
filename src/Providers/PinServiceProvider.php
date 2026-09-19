<?php

declare(strict_types=1);

namespace Pin\Providers;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\ServiceProvider;
use Override;
use Pin\Action\ActionServiceProvider;
use Pin\Auth\AuthServiceProvider;
use Pin\Cache\CacheServiceProvider;
use Pin\Console\Commands\IdeHelperCommand;
use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Crypt\CryptServiceProvider;
use Pin\Database\DatabaseServiceProvider;
use Pin\Database\MigrationServiceProvider;
use Pin\Debug\DebugServiceProvider;
use Pin\Errors\ErrorsServiceProvider;
use Pin\Faker\FakerServiceProvider;
use Pin\Http\Request;
use Pin\IdGenerator\IdGeneratorServiceProvider;
use Pin\Log\LogServiceProvider;
use Pin\Log\StackTraceNormalizer;
use Pin\Log\StackTracePolicy;
use Pin\Models\ModelServiceProvider;
use Pin\Password\PasswordServiceProvider;
use Pin\Scramble\ScrambleServiceProvider;
use Pin\Token\TokenServiceProvider;
use Pin\Tree\TreeServiceProvider;
use Pin\Validation\ValidationServiceProvider;

/**
 * Pin 框架核心服务提供者
 */
class PinServiceProvider extends ServiceProvider
{
    /**
     * 框架核心服务提供者列表
     *
     * @var class-string<ServiceProvider>[]
     */
    public const array PROVIDERS = [
        self::class,
        ActionServiceProvider::class,
        AuthServiceProvider::class,
        CacheServiceProvider::class,
        CryptServiceProvider::class,
        DatabaseServiceProvider::class,
        DebugServiceProvider::class,
        ErrorsServiceProvider::class,
        FakerServiceProvider::class,
        IdGeneratorServiceProvider::class,
        MigrationServiceProvider::class,
        ModelServiceProvider::class,
        PasswordServiceProvider::class,
        LogServiceProvider::class,
        ScrambleServiceProvider::class,
        TokenServiceProvider::class,
        TreeServiceProvider::class,
        ValidationServiceProvider::class,
    ];

    /**
     * 单例绑定
     *
     * @var class-string[]
     */
    public array $singletons = [
        StackTracePolicy::class,
        StackTraceNormalizer::class,
    ];

    /**
     * 启动框架服务
     */
    public function boot(): void
    {
        ThrottleRequests::shouldHashKeys(false);
        $this->registerTranslations();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishResources();
    }

    /**
     * 注册请求宏和开发辅助命令
     */
    #[Override]
    public function register(): void
    {
        Request::registerMacros();

        $this->commands([
            IdeHelperCommand::class,
            TableSchemasGenerateCommand::class,
        ]);
    }

    /**
     * 注册翻译资源
     */
    protected function registerTranslations(): void
    {
        $path = __DIR__.'/../../lang';

        $this->loadTranslationsFrom($path, 'pin');
        $this->loadJsonTranslationsFrom($path);
        $this->loadJsonTranslationsFrom($this->app->langPath('vendor/pin'));
    }

    /**
     * 发布语言包和配置
     */
    protected function publishResources(): void
    {
        $this->publishes([
            __DIR__.'/../../lang' => $this->app->langPath('vendor/pin'),
        ], 'pin-lang');

        $this->publishes([
            __DIR__.'/../../config/pin' => $this->app->configPath('pin'),
        ], 'pin-config');
    }
}
