<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Override;
use Pin\Auth\AuthenticationException as PinAuthenticationException;
use Pin\Auth\Guard;
use Pin\Exceptions\Exception;
use Pin\Exceptions\ValidationException;
use Pin\Http\ApiResponse;
use Throwable;

/**
 * JSON 异常响应
 */
trait HandlesJsonResponse
{
    #[Override]
    protected function convertExceptionToArray(Throwable $e): array
    {
        $data = $e instanceof ValidationException ? ['errors' => $e->getErrors()] : [];

        if (! app()->hasDebugModeEnabled()) {
            return $data;
        }

        $request = app()->request;

        return [
            ...$data,
            'class' => $e::class,
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'context' => $e instanceof Exception ? $e->getContext() : [],
            'trace' => [$e->getFile().':'.$e->getLine(), ...explode("\n", $e->getTraceAsString())],
            'post' => $request->post(),
            'headers' => $request->headers->all(),
            'server' => $request->server->all(),
        ];
    }

    /**
     * 获取响应头
     */
    protected function resolveHeaders(Throwable $e): array
    {
        return $this->isHttpException($e) || $e instanceof Exception
            ? $e->getHeaders()
            : [];
    }

    #[Override]
    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return ApiResponse::make(
            $this->resolveResponseCode($e),
            $this->resolveResponseMessage($e),
            $this->convertExceptionToArray($e),
        )->withStatusCode($this->resolveStatusCode($e))
            ->withHeaders($this->resolveHeaders($e))
            ->toResponse($request);
    }

    /**
     * 渲染 JSON 异常响应
     */
    protected function renderJsonException(Request $request, Throwable $e)
    {
        $e = match (true) {
            $e instanceof LaravelValidationException => new ValidationException($e),
            $e instanceof AuthenticationException => new PinAuthenticationException(
                code: (int) $request->attributes->get(Guard::UNAUTHENTICATED_CODE),
                previous: $e
            ),

            default => $e,
        };

        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        return $this->prepareJsonResponse($request, $e);
    }

    #[Override]
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->is('api/*') || parent::shouldReturnJson($request, $e);
    }
}
