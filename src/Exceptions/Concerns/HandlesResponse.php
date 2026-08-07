<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Http\Response;
use Override;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Pin\Http\ApiResponse;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * 非 JSON 响应渲染层
 *
 * 用于处理非 API 场景下的异常输出：
 * - 将异常转换为 HTTP 响应异常
 * - 在非 debug 模式下隐藏内部异常信息
 *
 * 作为 HTML / Web 响应的异常适配层
 */
trait HandlesResponse
{
    #[Override]
    protected function finalizeRenderedResponse($request, $response, Throwable $e)
    {
        if ($this->shouldReturnJson($request, $e) && ! ApiResponse::matches($response)) {
            $response = $this->renderJsonException($request, $e);
        }

        return parent::finalizeRenderedResponse($request, $response, $e);
    }

    #[Override]
    protected function prepareResponse($request, Throwable $e)
    {
        if (! app()->hasDebugModeEnabled()) {
            $e = new HttpException(
                $this->resolveStatusCode($e),
                $this->resolveResponseMessage($e),
                $e,
                $this->resolveHeaders($e),
                $e->getCode()
            );
        }

        return parent::prepareResponse($request, $e);
    }

    /**
     * 解析响应业务码
     */
    protected function resolveResponseCode(Throwable $e): int
    {
        $code = match (true) {
            // 自定义异常
            $e instanceof Exception => $e->getCode(),

            // HTTP 异常
            $this->isHttpException($e) => $e->getStatusCode(),

            // 服务器错误
            default => Errors::ServerError->code(),
        };

        // 服务器错误兜底
        return $code ?: Errors::ServerError->code();
    }

    /**
     * 解析响应信息
     */
    protected function resolveResponseMessage(Throwable $e): string
    {
        $message = match (true) {

            // 可疑请求 / 非法请求
            $e->getPrevious() instanceof SuspiciousOperationException => Errors::BadRequest->message(),

            // HTTP 异常
            $this->isHttpException($e) => Response::$statusTexts[$e->getStatusCode()] ?? $e->getMessage(),

            // 自定义业务异常 & 自定义错误
            $e instanceof Exception && ! empty($message = $e->getResponseMessage()) => $message,

            // 自定义业务异常 & 非 500 错误
            $e instanceof Exception && $e->getStatusCode() !== 500 => $e->getMessage(),

            // 调试模式
            app()->hasDebugModeEnabled() => $e->getMessage(),

            // 服务器错误
            default => Errors::ServerError->message(),
        };

        // 服务器错误兜底
        return $message ?: Errors::ServerError->message();
    }

    /**
     * 解析 HTTP 状态码
     *
     * 优先使用异常自带 statusCode，否则返回 500
     */
    protected function resolveStatusCode(Throwable $e): int
    {
        return method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
    }
}
