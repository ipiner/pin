<?php

declare(strict_types=1);

namespace Pin\Token\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use Pin\Errors\Errors;
use Pin\Token\Token;
use Throwable;

/**
 * 请求中缺少 Token 异常。
 */
class TokenMissingException extends TokenException implements ShouldntReport
{
    public function __construct(Token $token, ?Throwable $previous = null)
    {
        parent::__construct($token, Errors::TokenMissing, $previous);
    }
}
