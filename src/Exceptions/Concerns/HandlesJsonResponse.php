<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Override;
use Pin\Auth\Guard;
use Pin\Exceptions\Exception;
use Pin\Exceptions\ValidationException;
use Pin\Http\ApiResponse;
use Throwable;

/**
 * JSON 异常渲染层
 *
 * 用于将系统异常统一转换为 API JSON 响应：
 * - 支持业务异常 / HTTP 异常 / 框架异常
 * - 统一错误码与响应结构
 * - 支持自定义 Responsable 输出
 *
 * 作为 API Exception Handler 的核心输出层
 */
trait HandlesJsonResponse
{
    #[Override]
    protected function convertExceptionToArray(Throwable $e)
    {
        $exceptionArray = [];

        // 验证异常特殊处理
        if ($e instanceof ValidationException) {
            $exceptionArray['errors'] = $e->getErrors();
        }

        // debug 模式输出完整信息
        if (app()->hasDebugModeEnabled()) {
            $exceptionArray['class'] = get_class($e);
            $exceptionArray['code'] = $e->getCode();
            $exceptionArray['message'] = $e->getMessage();
            $exceptionArray['context'] = $e instanceof Exception ? $e->getContext() : [];
            $exceptionArray['trace'] = array_merge(
                [$e->getFile().':'.$e->getLine()],
                explode("\n", $e->getTraceAsString())
            );
            $exceptionArray['post'] = app()->request->post();
            $exceptionArray['headers'] = app()->request->headers->all();
            $exceptionArray['server'] = app()->request->server->all();
        }

        return $exceptionArray;
    }

    /**
     * 响应头解析
     */
    protected function resolveHeaders(Throwable $e): array
    {
        return $this->isHttpException($e) || $e instanceof Exception
            ? $e->getHeaders()
            : [];
    }

    #[Override]
    protected function prepareJsonResponse($request, Throwable $e)
    {
        $exceptionArray = $this->convertExceptionToArray($e);

        return ApiResponse::make(
            $this->resolveResponseCode($e),
            $this->resolveResponseMessage($e),
            $exceptionArray,
            ['caller' => implode(':', $this->resolveCaller($e))]
        )
            ->withStatusCode($this->resolveStatusCode($e))
            ->withHeaders($this->resolveHeaders($e))
            ->toResponse($request);
    }

    /**
     * JSON 异常渲染入口
     */
    protected function renderJsonException(Request $request, Throwable $e)
    {
        $e = match (true) {
            // Laravel 验证异常 → 自定义
            $e instanceof \Illuminate\Validation\ValidationException => new ValidationException($e),

            // 认证异常 → 自定义
            $e instanceof AuthenticationException => new \Pin\Auth\AuthenticationException(
                code: (int) $request->attributes->get(Guard::UNAUTHENTICATED_CODE)
            ),

            default => $e,
        };

        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        return $this->prepareJsonResponse($request, $e);
    }

    #[Override]
    protected function shouldReturnJson($request, Throwable $e)
    {
        return $request->is('api/*') || parent::shouldReturnJson($request, $e);
    }
}
