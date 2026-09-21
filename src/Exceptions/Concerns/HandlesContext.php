<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Support\Arr;
use Override;
use Pin\Exceptions\Exception;
use Pin\Support\Caller;
use Throwable;

/**
 * 异常日志上下文
 */
trait HandlesContext
{
    /**
     * 获取异常位置
     */
    protected function resolveCaller(Throwable $e): array
    {
        $caller = $e instanceof Exception
            ? $e->getCaller()
            : Caller::resolve($e->getTrace());

        return Arr::only($caller, ['file', 'line']);
    }

    #[Override]
    protected function buildExceptionContext(Throwable $e): array
    {
        return array_merge(
            parent::buildExceptionContext($e),
            method_exists($e, 'getContext') ? $e->getContext() : [],
            $this->resolveCaller($e)
        );
    }

    #[Override]
    protected function context(): array
    {
        return array_filter([
            'post' => app()->request->post(),
        ]);
    }
}
