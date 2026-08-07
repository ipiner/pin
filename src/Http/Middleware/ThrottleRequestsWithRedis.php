<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Pin\Support\Facades\Aes;
use Pin\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * ThrottleRequestsWithRedis 中间件（请求限流）
 *
 * 对限流响应头进行加密处理
 */
class ThrottleRequestsWithRedis extends \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis
{
    /**
     * 自定义限流响应头名称
     */
    public const HEADER_NAME = 'x-b1nzygq';

    /**
     * 解码限流响应头
     */
    public static function decodeHeaders(Response $response): array
    {
        try {
            $encoded = $response->headers->get(static::HEADER_NAME);

            // 如果存在加密值，则解密并按 '|' 分割成数组，否则返回空数组
            return $encoded ? Str::explodeToIntegers(Aes::decrypt($encoded), '|') : [];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * 编码限流响应头
     */
    public static function encodeHeaders(array $headers): string
    {
        return Aes::encrypt(implode('|', $headers));
    }

    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if (! $this->shouldRun()) {
            return $next($request);
        }

        return parent::handle(...func_get_args());
    }

    protected function shouldRun(): bool
    {
        return config('app.rate_limit.enabled') !== false;
    }

    /**
     * 获取限流响应头
     */
    protected function getHeaders($maxAttempts, $remainingAttempts, $retryAfter = null, ?Response $response = null)
    {
        $headers = parent::getHeaders($maxAttempts, $remainingAttempts, $retryAfter, $response);
        if (! $headers) {
            return [];
        }

        return [static::HEADER_NAME => static::encodeHeaders($headers)];
    }
}
