<?php

declare(strict_types=1);

namespace Pin\Auth;

use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Exceptions\Exception;
use Throwable;

/**
 * 认证异常。
 *
 * 用于表示当前请求未通过身份认证。异常会统一返回 HTTP 401 状态码，
 * 并在可能的情况下，将底层 Token 错误转换为更明确的认证错误。
 */
class AuthenticationException extends Exception
{
    public function __construct(string $message = '', int $code = 401, ?Throwable $previous = null)
    {
        $code = $code ?: 401;
        $err = $this->resolveAuthError($code);

        parent::__construct($message ?: '请登录', $err?->code() ?? $code, $previous);

        $this->withStatusCode(401)->withResponseMessage($err?->message());
    }

    /**
     * 将 Token 层错误转换为认证层错误。
     */
    protected function resolveAuthError(int $code): ?IError
    {
        return match ($code) {
            Errors::TokenExpired->code() => Errors::AuthTokenExpired,
            Errors::TokenInvalid->code() => Errors::AuthTokenInvalid,
            Errors::TokenMissing->code() => Errors::AuthTokenMissing,
            default => null,
        };
    }
}
