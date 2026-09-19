<?php

declare(strict_types=1);

namespace Pin\Http\Middleware\LogApiResponse;

/**
 * 响应日志记录策略
 */
trait HandlesLogging
{
    /**
     * 是否记录当前请求日志
     */
    protected function shouldLog(): bool
    {
        if (! $this->hasValidResponse() || $this->isExceptRoute()) {
            return false;
        }

        if ($this->isForceLogEnabled()) {
            return true;
        }

        return ! $this->isSuccess() || $this->isSlow();
    }

    /**
     * 是否强制记录日志
     */
    protected function isForceLogEnabled(): bool
    {
        $enabled = config('pin.logging.response.enabled');

        return app()->hasDebugModeEnabled()
            || $enabled === true
            || ($enabled !== false && $this->request->isRequest($enabled));
    }

    /**
     * 是否为排除路由
     */
    protected function isExceptRoute(): bool
    {
        $excepts = (array) config('pin.logging.response.except', []);

        return $excepts && $this->request->isRequest($excepts);
    }

    /**
     * 是否成功响应
     */
    protected function isSuccess(): bool
    {
        return ($this->responseData['code'] ?? -1) === 0;
    }

    /**
     * 是否慢请求
     */
    protected function isSlow(): bool
    {
        return $this->duration() >= $this->slowThreshold();
    }
}
