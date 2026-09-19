<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Pin\Errors\Errors;

/**
 * 连续重复字符验证
 */
trait ValidatesRepetitions
{
    /**
     * 最大连续重复字符长度
     */
    protected int $maxRepeatedCharacters = 5;

    /**
     * 设置最大连续重复字符长度
     */
    public function maxRepeatedCharacters(int $max): static
    {
        $this->maxRepeatedCharacters = $max;

        return $this;
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
}
