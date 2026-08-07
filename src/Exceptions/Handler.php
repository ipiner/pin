<?php

/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpMissingReturnTypeInspection */

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Override;
use Pin\Exceptions\Concerns\HandlesContext;
use Pin\Exceptions\Concerns\HandlesJsonResponse;
use Pin\Exceptions\Concerns\HandlesResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

/**
 * 应用异常处理器。
 *
 * 基于 Laravel 默认异常处理机制，提供统一的 API 响应、错误码映射和日志处理。
 */
class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    use HandlesContext,
        HandlesJsonResponse,
        HandlesResponse;

    /**
     * 日志级别映射
     */
    protected $levels = [
        ThrottleRequestsException::class => 'info',
        MethodNotAllowedHttpException::class => 'info',
    ];

    #[Override]
    public function register()
    {
        parent::register();

        $this->map(
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            fn ($e) => new ModelNotFoundException($e)
        );
    }

    #[Override]
    protected function mapLogLevel(Throwable $e)
    {
        return $e instanceof Exception ? $e->getLogLevel() : parent::mapLogLevel($e);
    }

    #[Override]
    protected function shouldntReport(Throwable $e)
    {
        return $e instanceof Exception && ! $e->getReport()
            || parent::shouldntReport($e);
    }
}
