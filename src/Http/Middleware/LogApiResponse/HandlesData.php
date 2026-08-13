<?php

namespace Pin\Http\Middleware\LogApiResponse;

/**
 * 响应数据策略控制层
 *
 * 用于控制请求/响应日志中是否记录敏感或大体量数据
 */
trait HandlesData
{
    /**
     * 是否记录请求 payload
     */
    protected function shouldIncludeRequestPayload(): bool
    {
        return app()->hasDebugModeEnabled()
            || ! $this->isSuccess()
            || config('pin.logging.response.include_request_payload', false);
    }

    /**
     * 是否记录 response data
     */
    protected function shouldIncludeData(): bool
    {
        $ignores = config('pin.logging.response.ignore_response_data');

        return ! in_array('*', $ignores)
            && ! $this->request->isRequest($ignores);
    }
}
