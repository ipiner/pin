<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException as EloquentModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Override;
use Pin\Exceptions\Concerns\HandlesContext;
use Pin\Exceptions\Concerns\HandlesJsonResponse;
use Pin\Exceptions\Concerns\HandlesResponse;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

/**
 * 应用异常处理器
 */
class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    use HandlesContext, HandlesJsonResponse, HandlesResponse;

    /**
     * 异常日志级别
     */
    protected $levels = [
        ThrottleRequestsException::class => LogLevel::INFO,
        MethodNotAllowedHttpException::class => LogLevel::INFO,
    ];

    #[Override]
    public function register(): void
    {
        parent::register();

        $this->map(
            EloquentModelNotFoundException::class,
            static fn (EloquentModelNotFoundException $e) => new ModelNotFoundException($e)
        );
    }

    #[Override]
    protected function mapLogLevel(Throwable $e): string
    {
        return $e instanceof Exception ? $e->getLogLevel() : parent::mapLogLevel($e);
    }

    #[Override]
    protected function shouldntReport(Throwable $e): bool
    {
        if ($e instanceof Exception && ! $e->getReport()) {
            return true;
        }

        return parent::shouldntReport($e);
    }
}
