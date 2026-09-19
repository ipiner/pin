<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Pin\Http\ApiResponse;

/**
 * 模拟数据响应异常。
 */
class FakeResponseException extends Exception implements Responsable
{
    /**
     * 不记录日志。
     */
    public ?bool $report = false;

    /**
     * 设置模拟数据。
     */
    public function __construct(protected array $data)
    {
        parent::__construct(message: '请求成功');
    }

    /**
     * 输出模拟数据。
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse($this->data, 200, [], ApiResponse::JSON_ENCODE_OPTIONS);
    }
}
