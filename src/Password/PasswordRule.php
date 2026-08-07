<?php

declare(strict_types=1);

namespace Pin\Password;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 密码验证规则
 */
class PasswordRule implements ValidationRule
{
    use Concerns\HandlesValidation,
        Concerns\ValidatesComposition,
        Concerns\ValidatesLength,
        Concerns\ValidatesRepetitions,
        Concerns\ValidatesSequences,
        Concerns\ValidatesWhitespace;

    public function __construct(protected bool $withErrorCode = true)
    {
    }
}
