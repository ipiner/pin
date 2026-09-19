<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Closure;
use Pin\Errors\IError;

/**
 * 密码验证处理
 */
trait HandlesValidation
{
    /**
     * 验证错误。
     *
     * @var array<int, string>
     */
    protected array $errors = [];

    /**
     * 待验证密码
     */
    protected string $value;

    /**
     * 验证密码
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->value = (string) $value;

        if ($this->passes()) {
            return;
        }

        foreach ($this->errors as $code => $message) {
            $fail($this->withErrorCode ? $code.'|'.$message : $message);
        }
    }

    /**
     * 检查是否通过验证
     */
    protected function passes(): bool
    {
        $this->errors = [];
        $this->validateMinLength();
        $this->validateMaxLength();
        $this->validateWhitespace();
        $this->validateMaxSequentialCharacters();
        $this->validateMaxRepeatedCharacters();

        if ($this->requiredCharacterTypes > 1) {
            $this->validateRequiredCharacterTypes();
        } else {
            $this->validateNumbers();
            $this->validateLetters();
            $this->validateLowercase();
            $this->validateUppercase();
            $this->validateMixedCase();
            $this->validateSymbols();
        }

        return ! $this->errors;
    }

    /**
     * 验证正则规则
     */
    protected function matchPattern(string $pattern, IError $error): int
    {
        return $this->value === '' || preg_match($pattern, $this->value)
            ? 0
            : $this->addError($error);
    }

    /**
     * 添加验证错误。
     *
     * @param  array<string, mixed>  $replacements  错误消息替换参数
     */
    protected function addError(IError $error, array $replacements = []): int
    {
        $code = $error->code();
        $this->errors[$code] = $error->message($replacements);

        return $code;
    }
}
