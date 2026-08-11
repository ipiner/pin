<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Support\Arr;
use Override;
use Pin\Exceptions\Exception;
use Pin\Support\Caller;
use Throwable;

/**
 * 异常上下文增强 Trait
 *
 * 用于扩展异常日志信息，统一补充：
 * - 异常发生位置（file / line）
 * - 自定义 context 数据
 * - 请求上下文信息
 *
 * 作为异常日志系统的上下文补充层（logging context enhancer）
 */
trait HandlesContext
{
    /**
     * 解析异常发生位置
     */
    protected function resolveCaller(Throwable $e): array
    {
        $caller = $e instanceof Exception
            ? $e->getCaller()
            : Caller::resolve($e->getTrace());

        return Arr::only($caller, ['file', 'line']);
    }

    #[Override]
    protected function buildExceptionContext(Throwable $e)
    {
        return array_merge(
            parent::buildExceptionContext($e),
            method_exists($e, 'getContext') ? $e->getContext() : [],
            $this->resolveCaller($e)
        );
    }

    #[Override]
    protected function context()
    {
        return array_filter([
            'post' => app()->request->post(),
        ]);
    }
}
