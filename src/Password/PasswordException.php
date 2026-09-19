<?php

declare(strict_types=1);

namespace Pin\Password;

use Override;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Psr\Log\LogLevel;

/**
 * 密码异常
 */
class PasswordException extends Exception
{
    #[Override]
    protected function initialize(): void
    {
        $this->withResponseMessage(Errors::PasswordInvalid->message())
            ->withStatusCode(422)
            ->withLogLevel(LogLevel::INFO)
            ->withReport();
    }
}
