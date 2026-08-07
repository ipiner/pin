<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Pin\Errors\Errors;
use Pin\Http\ApiResponse;

/**
 * 验证异常适配器
 *
 * 将 Laravel ValidationException 转换为统一业务异常：
 * - 错误码标准化
 * - 错误信息结构化
 * - 支持 API JSON 输出
 */
class ValidationException extends Exception implements Responsable
{
    /**
     * 包装 Laravel 验证异常并解析业务错误码
     */
    public function __construct(protected readonly \Illuminate\Validation\ValidationException $e)
    {
        [$code, $message] = $this->resolveCodeMessage($e->validator->errors()->first());
        parent::__construct($message, $code, $e);
        $this->withStatusCode($e->status)->withContext([
            'errors' => $e->validator->errors()->toArray(),
        ]);
    }

    /**
     * 解析错误码与错误信息
     */
    public static function resolveCodeMessage(string $error): array
    {
        // 错误码，自动获取错误信息
        if (ctype_digit($error)) {
            return [(int) $error, Errors::getMessage((int) $error)];
        }

        // 默认
        $defaultResult = [Errors::ValidationFailed->code(), $error];
        if (! str_contains($error, '|')) {
            return $defaultResult;
        }

        // 错误码|错误信息
        [$code, $message] = explode('|', $error, 2);
        $code = trim($code);
        $message = trim($message);

        // 不是错误码？返回默认错误
        if (! ctype_digit($code)) {
            return $defaultResult;
        }

        return [(int) $code, $message];
    }

    /**
     * 调用位置（映射原始 ValidationException）
     */
    public function getCaller(?string $file = null, ?int $line = null): array
    {
        return parent::getCaller($this->e->getFile(), $this->e->getLine());
    }

    /**
     * 验证错误列表
     */
    public function getErrors(): array
    {
        $result = [];
        foreach ($this->e->errors() as $field => $errors) {
            $result[$field] = [];
            foreach ($errors as $message) {
                $result[$field][] = $this->resolveCodeMessage($message)[1];
            }
        }

        return $result;
    }

    /**
     * 输出统一 API JSON 验证响应
     */
    public function toResponse($request): JsonResponse
    {
        return ApiResponse::make(
            $this->code,
            $this->getMessage(),
            ['errors' => $this->e->errors()]
        )
            ->withStatusCode($this->statusCode)
            ->withHeaders($this->headers)
            ->toResponse($request);
    }
}
