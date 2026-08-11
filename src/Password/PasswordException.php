<?php

declare(strict_types=1);

namespace Pin\Password;

use Override;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 密码加解密和校验相关异常。
 */
class PasswordException extends Exception
{
    #[Override]
    protected function initialize(): void
    {
        $this->withResponseMessage(Errors::PasswordInvalid->message())
            ->withStatusCode(422)
            ->withLogLevel('info')
            ->withReport();
    }
}
