<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Pin\Errors\Errors;

/**
 * 空白字符验证
 */
trait ValidatesWhitespace
{
    /**
     * 是否允许包含空白字符
     */
    protected bool $allowWhitespace = false;

    /**
     * 允许密码包含空白字符
     */
    public function allowWhitespace(): static
    {
        $this->allowWhitespace = true;

        return $this;
    }

    /**
     * 验证密码是否包含空白字符
     */
    protected function validateWhiteSpace(): int
    {
        return $this->allowWhitespace
            ? 0
            : $this->matchPattern('/^\S+$/', Errors::PasswordContainsWhitespace);
    }
}
