<?php

declare(strict_types=1);

namespace Pin\Http\Middleware\LogApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Pin\Http\ApiResponse;
use Pin\Support\Arr;
use Pin\Support\Json;

/**
 * 响应日志数据处理
 */
trait HandlesResponse
{
    /**
     * 响应数据
     */
    protected ?array $responseData = null;

    /**
     * 提取响应数据
     */
    protected function extractResponseData(): ?array
    {
        $response = $this->request->attributes->get(static::API_RESPONSE);

        if (! $response instanceof JsonResponse) {
            $response = $this->response;
        }

        if (! $response instanceof JsonResponse) {
            return null;
        }

        $data = $response->getData(true);

        return is_array($data) ? $data : null;
    }

    /**
     * 是否为有效 API 响应结构
     */
    protected function hasValidResponse(): bool
    {
        return ApiResponse::matches($this->responseData);
    }

    /**
     * 规范化响应数据
     */
    protected function normalizeResponse(): array
    {
        $response = Arr::maskSensitive([
            'code' => $this->responseData['code'],
            'message' => $this->responseData['message'],
        ]);
        $response['data'] = '...';

        if (! $this->shouldIncludeData()) {
            return $response;
        }

        $data = $this->responseData;
        unset($data['code'], $data['message']);
        $response['data'] = $this->truncateResponseData(Arr::maskSensitive($data));

        return $response;
    }

    /**
     * 截断过大的响应数据
     */
    protected function truncateResponseData(array $data): string|array
    {
        $json = Json::encode($data);
        $maxLength = (int) config('pin.logging.response.max_length', 10240);

        if (strlen($json) <= $maxLength) {
            return $data;
        }

        $length = Str::length($json, 'UTF-8');

        if ($length <= $maxLength) {
            return $data;
        }

        return Str::substr($json, 0, $maxLength).'(...'.($length - $maxLength).')';
    }
}
