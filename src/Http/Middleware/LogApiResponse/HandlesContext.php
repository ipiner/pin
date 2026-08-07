<?php

declare(strict_types=1);

namespace Pin\Http\Middleware\LogApiResponse;

use Pin\Http\Middleware\ThrottleRequestsWithRedis;
use Pin\Support\Timer;

/**
 * 日志上下文构建模块
 */
trait HandlesContext
{
    /**
     * 构建日志上下文数据
     */
    protected function buildLogContext(): array
    {
        return [
            'category' => 'api',

            // 是否成功响应（业务层 code === 0）
            'success' => $this->isSuccess(),

            // 请求总耗时（ms）
            'time' => $this->duration(),

            // 是否慢请求（>= slowThreshold）
            'slow' => $this->isSlow(),

            // PHP 内存使用情况
            'memory' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),

            // SQL 统计信息（QueryMonitor 提供）
            'sql_count' => $this->queryMonitor->profile->count ?? 0,
            'sql_time' => $this->queryMonitor->profile->time ?? 0,

            // HTTP 状态码
            'status' => $this->response->getStatusCode(),

            // payload
            ...array_filter([
                'rate_limit' => ThrottleRequestsWithRedis::decodeHeaders($this->response),
                'payload' => $this->shouldIncludeRequestPayload() ? $this->request->post() : null,
            ]),
        ];
    }

    /**
     * 获取请求执行耗时（毫秒）
     */
    protected function duration(): int
    {
        return $this->duration ??= Timer::durationSinceStartOfRequest()->milliseconds();
    }

    /**
     * 获取慢请求阈值（毫秒）
     *
     * 配置规则：
     * - pin.logging.response.slow_threshold
     *
     * 兼容两种输入方式：
     * - ≤ 10：视为“秒”，自动转 ms（如 2 => 2000ms）
     * - > 10：视为已是 ms
     */
    protected function slowThreshold(): int
    {
        $value = (float) config('pin.logging.response.slow_threshold', 2000);

        return $value <= 10
            ? (int) ($value * 1000)
            : (int) $value;
    }
}
