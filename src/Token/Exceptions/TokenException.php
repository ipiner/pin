<?php

declare(strict_types=1);

namespace Pin\Token\Exceptions;

use Pin\Errors\IError;
use Pin\Exceptions\Exception;
use Pin\Token\Token;
use Throwable;

/**
 * Token 解析和校验过程中的基础异常。
 */
class TokenException extends Exception
{
    public function __construct(public Token $token, IError $err, ?Throwable $previous = null)
    {
        parent::__construct($err, 0, $previous);
        $this->withContext(['payload' => $token->payload, 'raw' => $token->raw]);
    }
}
