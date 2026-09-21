<?php

declare(strict_types=1);

namespace Pin\Errors;

use BackedEnum;
use Pin\Exceptions\Exception;
use Throwable;

/**
 * 错误枚举接口
 */
interface IError extends BackedEnum
{
    /**
     * 获取业务错误码
     */
    public function code(): int;

    /**
     * 创建异常
     */
    public function exception(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null
    ): Exception;

    /**
     * 获取错误消息
     */
    public function message(array $replace = []): string;

    /**
     * 获取 HTTP 状态码
     */
    public function statusCode(): int;

    /**
     * 抛出异常
     */
    public function throw(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null
    ): never;
}
