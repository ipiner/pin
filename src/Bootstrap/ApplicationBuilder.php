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
 * Pin 应用构建器
 *
 * @mixin Builder
 */
class ApplicationBuilder
{
    /**
     * @var array<string, bool>
     */
    protected array $configured = [];

    /**
     * 应用实例
     */
    protected Application $app;

    public function __construct(protected Builder $builder)
    {
        $this->app = $builder->create();
    }

    /**
     * 转发构建器调用
     */
    public function __call(string $name, array $arguments): static
    {
        $this->builder->{$name}(...$arguments);
        $this->configured[$name] = true;

        return $this;
    }

    /**
     * 创建应用
     */
    public function create(): Application
    {
        foreach (['withMiddleware', 'withProviders', 'withExceptions'] as $method) {
            if (! isset($this->configured[$method])) {
                $this->{$method}();
            }
        }

        if (! isset($this->configured['withRouting'])) {
            $this->withRouting(
                web: $this->routePath('web'),
                api: $this->routePath('api'),
                commands: $this->routePath('console'),
                health: '/up',
                apiPrefix: '',
            );
        }

        return $this->app;
    }

    /**
     * 配置异常处理器
     *
     * @param  class-string|(Closure(Exceptions): mixed)|null  $handler
     * @param  (callable(Exceptions): mixed)|null  $using
     */
    public function withExceptions(
        string|Closure|null $handler = null,
        ?callable $using = null,
    ): static {
        if ($handler instanceof Closure) {
            $using = $handler;
            $handler = null;
        }

        $this->builder->withExceptions($using);
        $this->app->singleton(
            ExceptionHandler::class,
            $handler ?? Handler::class,
        );
        $this->configured['withExceptions'] = true;

        return $this;
    }

    /**
     * 配置中间件
     */
    public function withMiddleware(?callable $callback = null): static
    {
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
        $this->configured['withMiddleware'] = true;

        return $this;
    }

    /**
     * 配置服务提供者
     */
    public function withProviders(
        array $providers = [],
        bool $withBootstrapProviders = true,
    ): static {
        $this->builder->withProviders(
            [
                ...PinServiceProvider::PROVIDERS,
                ...$providers,
            ],
            $withBootstrapProviders,
        );
        $this->configured['withProviders'] = true;

        return $this;
    }

    /**
     * 获取路由文件路径
     */
    protected function routePath(string $name): ?string
    {
        $path = $this->app->basePath("routes/{$name}.php");

        return is_file($path) ? $path : null;
    }
}
