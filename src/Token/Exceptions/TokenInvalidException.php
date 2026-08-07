<?php

declare(strict_types=1);

namespace Pin\Token\Exceptions;

use Pin\Errors\Errors;
use Pin\Token\Token;
use Throwable;

/**
 * Token 内容非法或签名校验失败异常。
 */
class TokenInvalidException extends TokenException
{
    public function __construct(Token $token, ?Throwable $previous = null)
    {
        parent::__construct($token, Errors::TokenInvalid, $previous);
    }
}
