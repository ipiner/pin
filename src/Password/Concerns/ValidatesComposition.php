<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Pin\Errors\Errors;

/**
 * 密码组成规则验证
 */
trait ValidatesComposition
{
    /**
     * 是否要求包含小写字母
     */
    protected bool $lowers = false;

    /**
     * 是否要求包含大写字母
     */
    protected bool $uppers = false;

    /**
     * 是否要求包含字母
     */
    protected bool $letters = false;

    /**
     * 是否要求包含数字
     */
    protected bool $numbers = false;

    /**
     * 是否要求同时包含大小写字母
     */
    protected bool $mixedCase = false;

    /**
     * 是否要求包含特殊字符
     */
    protected bool $symbols = false;

    /**
     * 至少需要包含的字符类型数量
     */
    protected ?int $requiredCharacterTypes = null;

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
     * 要求密码同时包含大小写字母
     */
    public function mixedCase(): static
    {
        $this->mixedCase = true;

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
     * 验证密码是否同时包含大小写字母
     */
    protected function validateMixedCase(): int
    {
        return $this->mixedCase
            ? $this->matchPattern(
                '/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u',
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
        if ($this->requiredCharacterTypes === null) {
            return 0;
        }

        $this->numbers()->letters()->symbols();

        $count = count(array_filter([
            $this->validateNumbers(),
            $this->validateLetters(),
            $this->validateSymbols(),
        ]));

        // 清理单项缺失错误，仅保留组合类型错误
        unset(
            $this->errors[Errors::PasswordRequiresNumber->code()],
            $this->errors[Errors::PasswordRequiresLetter->code()],
            $this->errors[Errors::PasswordRequiresSymbol->code()],
        );

        if (
            $count === 0
            || $count === 1 && $this->requiredCharacterTypes === 2
        ) {
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
}
