<?php

declare(strict_types=1);

namespace Pin\Log;

use Throwable;

/**
 * 异常堆栈记录策略
 */
class StackTracePolicy
{
    /**
     * 是否记录异常堆栈
     */
    public function shouldInclude(Throwable $e): bool
    {
        if (! config('pin.logging.stack_trace.enabled') || $this->isExcludedException($e)) {
            return false;
        }

        return $this->isIncludedException($e);
    }

    /**
     * 是否排除异常
     */
    protected function isExcludedException(Throwable $e): bool
    {
        if ($e instanceof SkipTrace) {
            return true;
        }

        return array_any(
            config('pin.logging.stack_trace.exclude_exceptions', []),
            static fn ($exception) => $e instanceof $exception
        );
    }

    /**
     * 是否允许记录异常
     */
    protected function isIncludedException(Throwable $e): bool
    {
        $includes = config('pin.logging.stack_trace.include_exceptions', []);

        if (! $includes) {
            return true;
        }

        return array_any(
            $includes,
            static fn ($exception) => $e instanceof $exception
        );
    }
}
