<?php

declare(strict_types=1);

namespace Pin\Bootstrap;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\ApplicationBuilder as Builder;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Pin\Exceptions\Handler;
use Pin\Http\Middleware\LogApiResponse;
use Pin\Http\Middleware\ResponseHeaders;
use Pin\Http\Middleware\ThrottleRequestsWithRedis;
use Pin\Providers\PinServiceProvider;

/**
 * @mixin Builder
 */
class ApplicationBuilder
{
    /**
     * @var array<string, bool>
     */
    protected array $called = [];

    /**
     * Application 实例
     */
    protected Application $app;

    public function __construct(protected Builder $builder)
    {
        $this->app = $builder->create();
    }

    /**
     * 调用转发
     */
    public function __call(string $name, array $arguments): static
    {
        $this->called[$name] = true;

        $this->builder->{$name}(...$arguments);

        return $this;
    }

    /**
     * 返回 `Application` 实例
     */
    public function create(): Application
    {
        foreach (['withMiddleware', 'withProviders', 'withExceptions'] as $method) {
            if (! isset($this->called[$method])) {
                $this->{$method}();
            }
        }

        if (! isset($this->call['withRouting'])) {
            $path = $this->app->basePath();
            $this->builder->withRouting(
                web: is_file($file = $path.'/routes/web.php') ? $file : null,
                api: is_file($file = $path.'/routes/api.php') ? $file : null,
                commands: is_file($file = $path.'/routes/console.php') ? $file : null,
                apiPrefix: '',
            );
        }

        return $this->app;
    }

    /**
     * 异常处理器配置
     *
     * @param  string|(callable(Exceptions): mixed)|null  $handler  = null
     * @param  (callable(Exceptions): mixed)|null  $using
     */
    public function withExceptions(
        string|Closure|null $handler = null,
        ?callable $using = null
    ): static {
        $this->called['withExceptions'] = true;

        if ($handler instanceof Closure) {
            $using = $handler;
            $handler = null;
        }

        $this->builder->withExceptions($using);
        $this->app->singleton(
            ExceptionHandler::class,
            $handler ?? Handler::class,
        );

        return $this;
    }

    /**
     * 中间件配置
     */
    public function withMiddleware(?callable $callback = null): static
    {
        $this->called['withMiddleware'] = true;

        $this->builder->withMiddleware(function (Middleware $middleware) use ($callback) {
            $middleware->redirectGuestsTo(null)
                ->append([
                    LogApiResponse::class,
                    ResponseHeaders::class,
                ])
                ->alias([
                    'throttle' => ThrottleRequestsWithRedis::class,
                ]);

            if ($callback) {
                $callback($middleware);
            }
        });

        return $this;
    }

    /**
     * 服务提供者配置
     */
    public function withProviders(
        array $providers = [],
        bool $withBootstrapProviders = true
    ): static {
        $this->called['withProviders'] = true;

        $this->builder->withProviders(
            [
                ...PinServiceProvider::PROVIDERS,
                ...$providers,
            ],
            $withBootstrapProviders,
        );

        return $this;
    }
}
