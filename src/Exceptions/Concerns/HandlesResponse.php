<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Override;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Pin\Http\ApiResponse;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * 异常响应处理
 */
trait HandlesResponse
{
    #[Override]
    protected function finalizeRenderedResponse($request, $response, Throwable $e)
    {
        if (
            ! $e instanceof Responsable
            && $this->shouldReturnJson($request, $e)
            && ! ApiResponse::matches($response)
        ) {
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
     * 获取响应业务码
     */
    protected function resolveResponseCode(Throwable $e): int
    {
        $code = match (true) {
            $e instanceof Exception => $e->getCode(),
            $this->isHttpException($e) => $e->getStatusCode(),
            default => Errors::ServerError->code(),
        };

        return $code ?: Errors::ServerError->code();
    }

    /**
     * 获取响应消息
     */
    protected function resolveResponseMessage(Throwable $e): string
    {
        if ($e->getPrevious() instanceof SuspiciousOperationException) {
            return Errors::BadRequest->message() ?: Errors::ServerError->message();
        }

        $message = match (true) {
            $this->isHttpException($e) => Response::$statusTexts[$e->getStatusCode()]
                ?? $e->getMessage(),
            $e instanceof Exception && ($message = $e->getResponseMessage()) => $message,
            $e instanceof Exception && $e->getStatusCode() !== 500 => $e->getMessage(),
            app()->hasDebugModeEnabled() => $e->getMessage(),
            default => Errors::ServerError->message(),
        };

        return $message ?: Errors::ServerError->message();
    }

    /**
     * 获取 HTTP 状态码
     */
    protected function resolveStatusCode(Throwable $e): int
    {
        return method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
    }
}
