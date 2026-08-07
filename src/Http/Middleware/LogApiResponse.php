<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pin\Database\QueryMonitor;
use Pin\Http\Middleware\LogApiResponse\HandlesContext;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * API JSON 响应日志中间件
 */
class LogApiResponse
{
    /**
     * 响应日志 request attribute key
     */
    public const string API_RESPONSE = 'LogApiResponse';

    use HandlesContext,
        LogApiResponse\HandlesData,
        LogApiResponse\HandlesLogging,
        LogApiResponse\HandlesResponse;

    /**
     * 当前 HTTP 请求实例
     */
    protected Request $request;

    /**
     * 当前 HTTP 响应实例
     */
    protected Response $response;

    /**
     * 注入 SQL 查询监控器
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
     * 请求结束后记录 API 响应日志
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $this->request = $request;
            $this->response = $response;
            $this->responseData = $this->extractResponseData();

            if ($this->shouldLog()) {
                $this->logResponse();
            }

        } catch (Throwable $e) {
            // 防止日志系统异常影响主请求
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
     * 根据日志上下文决定日志级别
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
            // HTTP 5xx 系统异常
            $context['status'] >= 500 => LogLevel::ERROR,

            // 业务失败（code !== 0）
            ! $context['success'] => LogLevel::INFO,

            // 慢响应
            $context['slow'] => LogLevel::NOTICE,

            // 正常请求
            default => LogLevel::DEBUG,
        };
    }
}
