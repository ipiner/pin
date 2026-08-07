<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Illuminate\Support\Facades\Route;
use Pin\Module\ModuleInspector;
use Pin\Route\Attributes\Handler;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Routable;
use Pin\Route\RouteRegistry;

/**
 * HasRegister
 *
 * 提供 Route Enum 的路由注册能力
 */
trait HasRegister
{
    /**
     * 注册当前枚举中的所有路由
     */
    public static function registerRoutes(): void
    {
        static::addRoutes();
    }

    /**
     * 注册路由
     *
     * @param  callable|array|string  $handler  路由处理器
     * @param  string|string[]|null  $middlewares  附加中间件
     */
    public function register(
        callable|array|string $handler,
        string|array|null $middlewares = null,
    ): \Illuminate\Routing\Route {
        $info = $this->definition();
        $route = Route::addRoute($info->method, $info->uri, $handler)->name($info->name);

        $middlewares = $middlewares ?? $this->middlewares();
        if ($middlewares) {
            $route->middleware($middlewares);
        }

        RouteRegistry::bind($this, $route);

        return $route;
    }

    /**
     * 生成当前路由 URL。
     *
     * @param  array<string, mixed>|null  $params  路由参数
     * @param  bool  $absolute  是否生成绝对 URL
     * @return string 生成后的路由 URL
     */
    public function route(?array $params = null, bool $absolute = true): string
    {
        return route($this->definition()->name, (array) $params, $absolute);
    }

    /**
     * 把所有路由加进路由表
     */
    protected static function addRoutes(): void
    {
        foreach (static::cases() as $route) {
            /** @var Routable $route */
            $route->register($route->handler());
        }
    }

    /**
     * 推导当前 Route 对应的 Controller 类名。
     *
     * @return class-string
     */
    protected function controller(): string
    {
        return ModuleInspector::make(static::class)->controller();
    }

    /**
     * 获取路由处理器。
     *
     * @return array{0: class-string, 1: string}
     */
    protected function handler(): mixed
    {
        return $this->attribute(Handler::class)?->value
            ?? [$this->controller(), lcfirst($this->name)];
    }

    /**
     * 获取当前路由声明的 Middleware。
     */
    protected function middlewares(): string|array|null
    {
        return $this->attribute(Middleware::class)?->value;
    }
}
