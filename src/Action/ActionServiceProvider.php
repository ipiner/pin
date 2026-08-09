<?php

declare(strict_types=1);

namespace Pin\Action;

use Illuminate\Http\Request;
use Pin\Exceptions\FakeResponseException;
use Pin\Support\ServiceProvider;

/**
 * Action 服务提供者
 *
 * 在容器解析 Action 后注入请求上下文，并支持 fake 响应调试。
 */
class ActionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->afterResolving(Action::class, function (Action $action) {
            // CLI / Test 环境下可能不存在 request
            if ($this->app->bound('request')) {
                $this->resolvedAction($this->app['request'], $action);
            }
        });
    }

    /**
     * 容器解析完成后的 Action 初始化
     *
     * - 注入请求数据（payload）
     * - 注入路由参数和路由名称
     * - 检测 Fake Response
     */
    protected function resolvedAction(Request $request, Action $action): void
    {
        $route = $request->route();

        /**
         * 先填充 payload/context，再执行 boot()，以确保 boot() 中能够访问：
         *
         * - payload
         * - route parameters
         * - route name
         */
        $action->payload($request->all())
            ->context($route?->parameters() ?: [])
            ->context('__route_name__', $route?->getName())
            ->boot();

        // fake response
        if (config('pin.action.fake_response_enabled') && $request->input('_fake')) {
            throw new FakeResponseException($action->fakeData());
        }
    }
}
