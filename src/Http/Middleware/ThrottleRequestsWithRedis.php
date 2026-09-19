<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis as BaseThrottleRequestsWithRedis;
use Override;
use Pin\Support\Facades\Aes;
use Pin\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Redis 请求限流
 */
class ThrottleRequestsWithRedis extends BaseThrottleRequestsWithRedis
{
    /**
     * 自定义限流响应头名称
     */
    public const string HEADER_NAME = 'x-b1nzygq';

    /**
     * 解码限流响应头。
     *
     * @return int[]
     */
    public static function decodeHeaders(Response $response): array
    {
        try {
            $encoded = $response->headers->get(static::HEADER_NAME);

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

    /**
     * 处理请求
     */
    #[Override]
    public function handle(
        $request,
        Closure $next,
        $maxAttempts = 60,
        $decayMinutes = 1,
        $prefix = ''
    ): mixed {
        if (! $this->shouldRun()) {
            return $next($request);
        }

        return parent::handle(...func_get_args());
    }

    /**
     * 是否启用限流
     */
    protected function shouldRun(): bool
    {
        return config('app.rate_limit.enabled') !== false;
    }

    /**
     * 获取加密的限流响应头
     */
    #[Override]
    protected function getHeaders(
        $maxAttempts,
        $remainingAttempts,
        $retryAfter = null,
        ?Response $response = null
    ): array {
        $headers = parent::getHeaders($maxAttempts, $remainingAttempts, $retryAfter, $response);

        if (! $headers) {
            return [];
        }

        return [static::HEADER_NAME => static::encodeHeaders($headers)];
    }
}
