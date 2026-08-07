<?php

namespace Pin\Http\Middleware\LogApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Pin\Http\ApiResponse;
use Pin\Http\Middleware\LogApiResponse;
use Pin\Support\Arr;
use Pin\Support\Json;

/**
 * API 响应处理模块
 *
 * 用于提取与规范化 API 响应数据
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
        $attr = $this->request->attributes->get(LogApiResponse::API_RESPONSE);

        if ($attr && $attr instanceof JsonResponse) {
            return $attr->getData(true);
        }

        if ($this->response instanceof JsonResponse) {
            return $this->response->getData(true);
        }

        return null;
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
        // 对响应数据进行敏感字段脱敏（如 password / token 等）
        $data = Arr::maskSensitive($this->responseData);

        $code = $data['code'] ?? null;
        $message = $data['message'] ?? null;

        // 从 data 中移除
        unset($data['code'], $data['message']);

        return [
            'code' => $code,
            'message' => $message,

            // 根据策略决定是否记录完整 data
            'data' => $this->shouldIncludeData()
                ? $this->truncateResponseData($data)
                : '...',
        ];
    }

    /**
     * 截断过大的响应数据
     */
    protected function truncateResponseData(array $data): string|array
    {
        $json = Json::encode($data);
        $length = Str::length($json, 'UTF-8');

        $maxLength = config('pin.logging.response.max_length', 10240);
        $exceed = $length - $maxLength;

        // 未超限，直接返回原结构
        if ($exceed <= 0) {
            return $data;
        }

        // 超限则截断并标记损失长度
        return Str::substr($json, 0, $maxLength).'(...'.$exceed.')';
    }
}
