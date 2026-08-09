<?php

declare(strict_types=1);

namespace Pin\Providers;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\ServiceProvider;
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
 *
 * 汇总注册框架内置服务、命令、宏和错误页资源。
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
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        StackTracePolicy::class,
        StackTraceNormalizer::class,
    ];

    /**
     * 发布框架错误页资源
     */
    public function boot(): void
    {
        ThrottleRequests::shouldHashKeys(false);

        $path = __DIR__.'/../../lang';
        $this->loadTranslationsFrom($path, 'pin');
        $this->loadJsonTranslationsFrom($path);
        $this->publishes([
            $path => $this->app->langPath('vendor/pin'),
        ], 'pin-lang');

        $this->publishes([
            __DIR__.'/../../config/pin' => config_path('pin'),
        ], 'pin-config');
    }

    /**
     * 注册请求宏和开发辅助命令
     */
    public function register(): void
    {
        // Illuminate\Http\Request 自定义宏
        Request::registerMacros();

        // 自定义命令
        $this->commands([
            IdeHelperCommand::class,
            TableSchemasGenerateCommand::class,
        ]);
    }
}
