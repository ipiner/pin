<?php

declare(strict_types=1);

namespace Pin\Token\Exceptions;

use Pin\Errors\Errors;
use Pin\Token\Token;
use Throwable;

/**
 * Token 无效。
 */
class TokenInvalidException extends TokenException
{
    public function __construct(Token $token, ?Throwable $previous = null)
    {
        parent::__construct($token, Errors::TokenInvalid, $previous);
    }
}
