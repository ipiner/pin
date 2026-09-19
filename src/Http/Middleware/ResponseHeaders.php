<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pin\Database\QueryMonitor;
use Pin\Support\Timer;
use Symfony\Component\HttpFoundation\Response;

/**
 * 请求统计响应头
 */
class ResponseHeaders
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $profile = app(QueryMonitor::class)->profile;

        // 请求 ID.请求耗时（毫秒）.SQL 次数.SQL 耗时（毫秒）
        $response->headers->set('x-request', sprintf(
            '%s.%d.%d.%d',
            app()->getRequestId(),
            Timer::durationSinceStartOfRequest(
                $request->server('REQUEST_TIME_FLOAT')
            )->milliseconds(),
            $profile->count,
            $profile->time
        ));

        return $response;
    }
}
