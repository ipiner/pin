<?php

declare(strict_types=1);

namespace Pin\Log;

use Throwable;

/**
 * Stack Trace Policy
 *
 * 用于决定某个异常（Throwable）是否应记录 stack trace。
 */
class StackTracePolicy
{
    /**
     * 判断指定异常是否应记录 stack trace。
     * ```
     *
     * @param  Throwable  $e  当前异常对象
     */
    public function shouldInclude(Throwable $e): bool
    {
        if (
            ! config('pin.logging.stack_trace.enabled') // 全局禁用
            || $this->isExcludedException($e) // 排除
        ) {
            return false;
        }

        return $this->isIncludedException($e);
    }

    /**
     * 判断异常是否属于排除列表
     */
    protected function isExcludedException(Throwable $e): bool
    {
        // 异常主动声明跳过 trace
        if ($e instanceof SkipTrace) {
            return true;
        }

        return array_any(
            config('pin.logging.stack_trace.exclude_exceptions'),
            fn ($exception) => $e instanceof $exception
        );
    }

    /**
     * 判断异常是否属于允许记录 trace 的异常
     */
    protected function isIncludedException(Throwable $e): bool
    {
        $includes = config('pin.logging.stack_trace.include_exceptions');

        // 空白名单：默认允许全部异常
        if ($includes === []) {
            return true;
        }

        return array_any(
            $includes,
            fn ($exception) => $e instanceof $exception
        );
    }
}
