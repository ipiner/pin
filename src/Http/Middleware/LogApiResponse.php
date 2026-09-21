<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pin\Database\QueryMonitor;
use Pin\Http\Middleware\LogApiResponse\HandlesContext;
use Pin\Http\Middleware\LogApiResponse\HandlesData;
use Pin\Http\Middleware\LogApiResponse\HandlesLogging;
use Pin\Http\Middleware\LogApiResponse\HandlesResponse;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * API JSON 响应日志中间件
 */
class LogApiResponse
{
    use HandlesContext;
    use HandlesData;
    use HandlesLogging;
    use HandlesResponse;

    /**
     * 响应日志的请求属性键
     */
    public const string API_RESPONSE = 'LogApiResponse';

    /**
     * 当前请求
     */
    protected Request $request;

    /**
     * 当前响应
     */
    protected Response $response;

    /**
     * 构造函数
     */
    public function __construct(protected QueryMonitor $queryMonitor)
    {
    }

    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request);
    }

    /**
     * 记录响应日志
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $this->request = $request;
            $this->response = $response;
            $this->duration = null;
            $this->responseData = $this->extractResponseData();

            if (! $this->shouldLog()) {
                return;
            }

            $this->logResponse();
        } catch (Throwable $e) {
            Log::channel('app')->error('LogApiResponse error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * 写入日志
     */
    protected function logResponse(): void
    {
        $response = $this->normalizeResponse();
        $context = $this->buildLogContext();

        Log::channel('api')->log(
            $this->resolveLogLevel($context),
            $response['message'],
            [
                ...$context,
                'code' => $response['code'],
                'message' => $response['message'],
                'data' => $response['data'],
            ]
        );
    }

    /**
     * 解析日志级别
     *
     * @param  array{
     *     status: int,
     *     success: bool,
     *     slow: bool
     * }  $context
     */
    protected function resolveLogLevel(array $context): string
    {
        return match (true) {
            $context['status'] >= 500 => LogLevel::ERROR,
            ! $context['success'] => LogLevel::INFO,
            $context['slow'] => LogLevel::NOTICE,
            default => LogLevel::DEBUG,
        };
    }
}
