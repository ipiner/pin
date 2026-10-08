<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;
use Pin\Errors\Errors;
use Pin\Errors\IError;

/**
 * 密码验证规则
 */
class Password implements ValidationRule
{
    /**
     * 是否允许包含空白字符
     */
    protected bool $allowWhitespace = false;

    /**
     * 验证错误
     *
     * @var array<int, string>
     */
    protected array $errors = [];

    /**
     * 是否要求包含字母
     */
    protected bool $letters = false;

    /**
     * 是否要求包含小写字母
     */
    protected bool $lowers = false;

    /**
     * 密码最大长度
     */
    protected int $max = 32;

    /**
     * 最大连续重复字符长度
     */
    protected int $maxRepeatedCharacters = 5;

    /**
     * 最大连续顺序字符长度
     */
    protected int $maxSequentialCharacters = 5;

    /**
     * 密码最小长度
     */
    protected int $min = 8;

    /**
     * 是否要求同时包含大小写字母
     */
    protected bool $mixedCase = false;

    /**
     * 是否要求包含数字
     */
    protected bool $numbers = false;

    /**
     * 至少需要包含的字符类型数量
     */
    protected ?int $requiredCharacterTypes = null;

    /**
     * 是否要求包含特殊字符
     */
    protected bool $symbols = false;

    /**
     * 是否要求包含大写字母
     */
    protected bool $uppers = false;

    /**
     * 待验证密码
     */
    protected string $value;

    public function __construct(protected bool $withErrorCode = true)
    {
    }

    /**
     * 允许密码包含空白字符
     */
    public function allowWhitespace(): static
    {
        $this->allowWhitespace = true;

        return $this;
    }

    /**
     * 要求密码包含字母
     */
    public function letters(): static
    {
        $this->letters = true;

        return $this;
    }

    /**
     * 要求密码包含小写字母
     */
    public function lowers(): static
    {
        $this->lowers = true;

        return $this;
    }

    /**
     * 设置密码最大长度
     */
    public function max(int $max): static
    {
        $this->max = $max;

        return $this;
    }

    /**
     * 设置最大连续重复字符长度
     */
    public function maxRepeatedCharacters(int $max): static
    {
        $this->maxRepeatedCharacters = $max;

        return $this;
    }

    /**
     * 设置最大连续顺序字符长度
     */
    public function maxSequentialCharacters(int $max): static
    {
        $this->maxSequentialCharacters = $max;

        return $this;
    }

    /**
     * 设置密码最小长度
     */
    public function min(int $min): static
    {
        $this->min = $min;

        return $this;
    }

    /**
     * 要求密码同时包含大小写字母
     */
    public function mixedCase(): static
    {
        $this->mixedCase = true;

        return $this;
    }

    /**
     * 要求密码包含数字
     */
    public function numbers(): static
    {
        $this->numbers = true;

        return $this;
    }

    /**
     * 设置密码至少需要包含的字符类型数量
     */
    public function requiredCharacterTypes(int $count): static
    {
        $this->requiredCharacterTypes = $count;

        return $this;
    }

    /**
     * 要求密码包含特殊字符
     */
    public function symbols(): static
    {
        $this->symbols = true;

        return $this;
    }

    /**
     * 要求密码包含大写字母
     */
    public function uppers(): static
    {
        $this->uppers = true;

        return $this;
    }

    /**
     * 验证密码
     */
    #[Override]
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
     * 添加验证错误
     *
     * @param  array<string, mixed>  $replacements  错误消息替换参数
     */
    protected function addError(IError $error, array $replacements = []): int
    {
        $code = $error->code();
        $this->errors[$code] = $error->message($replacements);

        return $code;
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
     * 验证密码是否包含字母
     */
    protected function validateLetters(): int
    {
        return $this->letters
            ? $this->matchPattern('/\pL/u', Errors::PasswordRequiresLetter)
            : 0;
    }

    /**
     * 验证密码是否包含小写字母
     */
    protected function validateLowercase(): int
    {
        return $this->lowers
            ? $this->matchPattern('/[a-z]/', Errors::PasswordRequiresLowercase)
            : 0;
    }

    /**
     * 验证密码最大长度
     */
    protected function validateMaxLength(): int
    {
        if (strlen($this->value) > $this->max) {
            return $this->addError(Errors::PasswordTooLong, ['max' => $this->max]);
        }

        return 0;
    }

    /**
     * 验证密码是否包含连续重复字符
     */
    protected function validateMaxRepeatedCharacters(): int
    {
        $repeated = 0;

        for ($index = 0, $length = strlen($this->value); $index < $length; $index++) {
            $repeated = $index > 0 && $this->value[$index] === $this->value[$index - 1]
                ? $repeated + 1
                : 1;

            if ($repeated >= $this->maxRepeatedCharacters) {
                return $this->addError(Errors::PasswordTooManyRepeats, [
                    'size' => $this->maxRepeatedCharacters,
                ]);
            }
        }

        return 0;
    }

    /**
     * 验证密码是否包含连续顺序字符
     */
    protected function validateMaxSequentialCharacters(): int
    {
        $ascending = 1;
        $descending = 1;

        for ($index = 0, $length = strlen($this->value); $index < $length; $index++) {
            if ($index > 0) {
                $difference = ord($this->value[$index]) - ord($this->value[$index - 1]);
                $ascending = $difference === 1 ? $ascending + 1 : 1;
                $descending = $difference === -1 ? $descending + 1 : 1;
            }

            if (
                $ascending >= $this->maxSequentialCharacters
                || $descending >= $this->maxSequentialCharacters
            ) {
                return $this->addError(Errors::PasswordSequenceTooLong, [
                    'size' => $this->maxSequentialCharacters,
                ]);
            }
        }

        return 0;
    }

    /**
     * 验证密码最小长度
     */
    protected function validateMinLength(): int
    {
        if (strlen($this->value) < $this->min) {
            return $this->addError(Errors::PasswordTooShort, ['min' => $this->min]);
        }

        return 0;
    }

    /**
     * 验证密码是否同时包含大小写字母
     */
    protected function validateMixedCase(): int
    {
        return $this->mixedCase
            ? $this->matchPattern(
                '/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/us',
                Errors::PasswordRequiresMixedCase
            )
            : 0;
    }

    /**
     * 验证密码是否包含数字
     */
    protected function validateNumbers(): int
    {
        return $this->numbers
            ? $this->matchPattern('/\d/', Errors::PasswordRequiresNumber)
            : 0;
    }

    /**
     * 验证密码字符类型数量
     */
    protected function validateRequiredCharacterTypes(): int
    {
        if (! $this->requiredCharacterTypes || $this->value === '') {
            return 0;
        }

        $count = preg_match('/\d/', $this->value)
            + preg_match('/\pL/u', $this->value)
            + preg_match('/\p{Z}|\p{S}|\p{P}/u', $this->value);

        if ($count === 3 || ($count === 2 && $this->requiredCharacterTypes === 2)) {
            return 0;
        }

        return $this->requiredCharacterTypes === 2
            ? $this->addError(Errors::PasswordInsufficientTypes)
            : $this->addError(Errors::PasswordRequiresAllTypes);
    }

    /**
     * 验证密码是否包含特殊字符
     */
    protected function validateSymbols(): int
    {
        return $this->symbols
            ? $this->matchPattern('/\p{Z}|\p{S}|\p{P}/u', Errors::PasswordRequiresSymbol)
            : 0;
    }

    /**
     * 验证密码是否包含大写字母
     */
    protected function validateUppercase(): int
    {
        return $this->uppers
            ? $this->matchPattern('/[A-Z]/', Errors::PasswordRequiresUppercase)
            : 0;
    }

    /**
     * 验证密码是否包含空白字符
     */
    protected function validateWhitespace(): int
    {
        return $this->allowWhitespace
            ? 0
            : $this->matchPattern('/\A\S+\z/', Errors::PasswordContainsWhitespace);
    }
}
