<?php

declare(strict_types=1);

namespace Pin\Errors;

use BackedEnum;
use Pin\Exceptions\Exception;
use Throwable;

/**
 * 错误契约接口（Error Contract）
 *
 * 定义统一的业务错误行为规范：
 * - 错误码（code）
 * - 错误消息（message）
 * - HTTP 状态码（statusCode）
 * - 异常构建能力（exception）
 * - 直接抛出能力（throw）
 */
interface IError extends BackedEnum
{
    /**
     * 获取业务错误码
     */
    public function code(): int;

    /**
     * 构建异常实例
     */
    public function exception(?string $message = null, ?int $code = null, ?Throwable $previous = null): Exception;

    /**
     * 错误消息
     *
     * 支持占位符替换与国际化
     */
    public function message(array $replace = []): string;

    /**
     * 获取 HTTP 状态码
     */
    public function statusCode(): int;

    /**
     * 抛出当前错误
     */
    public function throw(?string $message = null, ?int $code = null, ?Throwable $previous = null);
}
