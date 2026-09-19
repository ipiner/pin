<?php

declare(strict_types=1);

namespace Pin\Http\Middleware\LogApiResponse;

use Pin\Http\Middleware\ThrottleRequestsWithRedis;
use Pin\Support\Arr;
use Pin\Support\Timer;

/**
 * 响应日志上下文
 */
trait HandlesContext
{
    /**
     * 请求耗时（毫秒）
     */
    protected ?int $duration = null;

    /**
     * 构建日志上下文
     */
    protected function buildLogContext(): array
    {
        return [
            'category' => 'api',
            'success' => $this->isSuccess(),
            'time' => $this->duration(),
            'slow' => $this->isSlow(),
            'memory' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'sql_count' => $this->queryMonitor->profile->count,
            'sql_time' => $this->queryMonitor->profile->time,
            'status' => $this->response->getStatusCode(),
            ...array_filter([
                'rate_limit' => ThrottleRequestsWithRedis::decodeHeaders($this->response),
                'payload' => $this->shouldIncludeRequestPayload()
                    ? Arr::maskSensitive($this->request->post())
                    : null,
            ]),
        ];
    }

    /**
     * 获取请求耗时（毫秒）
     */
    protected function duration(): int
    {
        return $this->duration ??= Timer::durationSinceStartOfRequest(
            $this->request->server('REQUEST_TIME_FLOAT')
        )->milliseconds();
    }

    /**
     * 获取慢请求阈值（毫秒），配置值不超过 10 时按秒换算。
     */
    protected function slowThreshold(): int
    {
        $value = (float) config('pin.logging.response.slow_threshold', 2000);

        return (int) ($value <= 10 ? $value * 1000 : $value);
    }
}
