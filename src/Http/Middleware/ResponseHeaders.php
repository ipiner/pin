<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pin\Database\QueryMonitor;
use Pin\Support\Timer;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseHeaders 中间件
 */
class ResponseHeaders
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // 先执行请求并获取响应，再通过 tap 修改响应头
        return tap($next($request), function (Response $response) {
            $profile = app(QueryMonitor::class)->profile;

            // 设置自定义调试响应头 x-request
            // 格式：请求ID.请求耗时(毫秒).SQL执行次数.SQL总耗时(毫秒)
            $response->headers->set(
                'x-request',
                sprintf(
                    '%s.%d.%d.%d',
                    app()->getRequestId(), // 请求唯一ID
                    Timer::durationSinceStartOfRequest()->milliseconds(), // 请求耗时（毫秒）
                    $profile->count, // SQL 查询次数
                    $profile->time // SQL 总耗时（毫秒）
                )
            );
        });
    }
}
