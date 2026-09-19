<?php

declare(strict_types=1);

namespace Pin\Http\Middleware\LogApiResponse;

/**
 * 日志数据记录策略
 */
trait HandlesData
{
    /**
     * 是否记录请求体
     */
    protected function shouldIncludeRequestPayload(): bool
    {
        return app()->hasDebugModeEnabled()
            || ! $this->isSuccess()
            || config('pin.logging.response.include_request_payload', false);
    }

    /**
     * 是否记录响应数据
     */
    protected function shouldIncludeData(): bool
    {
        $ignores = config('pin.logging.response.ignore_response_data', []);

        return ! in_array('*', $ignores, true)
            && ! $this->request->isRequest($ignores);
    }
}
