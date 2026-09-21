<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Override;
use Pin\Errors\Errors;
use Pin\Http\ApiResponse;

/**
 * 验证异常
 */
class ValidationException extends Exception implements Responsable
{
    /**
     * 包装验证异常
     */
    public function __construct(protected readonly LaravelValidationException $e)
    {
        $errors = $e->validator->errors();
        [$code, $message] = static::resolveCodeMessage($errors->first());

        parent::__construct($message, $code, $e);

        $this->withStatusCode($e->status)->withContext(['errors' => $errors->toArray()]);
    }

    /**
     * 解析错误码与消息
     *
     * @return array{int, string}
     */
    public static function resolveCodeMessage(string $error): array
    {
        if (ctype_digit($error)) {
            return [(int) $error, Errors::getMessage((int) $error)];
        }

        if (! str_contains($error, '|')) {
            return [Errors::ValidationFailed->code(), $error];
        }

        [$code, $message] = explode('|', $error, 2);
        $code = trim($code);

        return ctype_digit($code)
            ? [(int) $code, trim($message)]
            : [Errors::ValidationFailed->code(), $error];
    }

    /**
     * 获取验证异常位置
     */
    #[Override]
    public function getCaller(?string $file = null, ?int $line = null): array
    {
        return parent::getCaller($file ?: $this->e->getFile(), $line ?: $this->e->getLine());
    }

    /**
     * 获取字段错误消息
     *
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        $result = [];

        foreach ($this->e->errors() as $field => $errors) {
            $result[$field] = array_map(
                static fn (string $message) => static::resolveCodeMessage($message)[1],
                $errors
            );
        }

        return $result;
    }

    /**
     * 输出验证响应
     */
    public function toResponse($request): JsonResponse
    {
        return ApiResponse::make(
            $this->getCode(),
            $this->getResponseMessage() ?: $this->getMessage(),
            ['errors' => $this->getErrors()]
        )->withStatusCode($this->getStatusCode())
            ->withHeaders($this->getHeaders())
            ->toResponse($request);
    }
}
