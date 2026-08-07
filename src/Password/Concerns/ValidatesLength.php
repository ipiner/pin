<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Pin\Errors\Errors;

/**
 * 密码长度校验
 */
trait ValidatesLength
{
    /**
     * 密码最小长度
     */
    protected int $min = 8;

    /**
     * 密码最大长度
     */
    protected int $max = 32;

    /**
     * 设置密码最大长度
     */
    public function max(int $max): static
    {
        $this->max = $max;

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
     * 验证密码最小长度
     */
    protected function validateMinLength(): int
    {
        if (strlen($this->value) < $this->min) {
            return $this->addError(Errors::PasswordTooShort, ['min' => $this->min]);
        }

        return 0;
    }
}
