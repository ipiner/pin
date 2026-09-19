<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Pin\Errors\Errors;

/**
 * 连续顺序字符验证
 */
trait ValidatesSequences
{
    /**
     * 最大连续顺序字符长度
     */
    protected int $maxSequentialCharacters = 5;

    /**
     * 设置最大连续顺序字符长度
     */
    public function maxSequentialCharacters(int $max): static
    {
        $this->maxSequentialCharacters = $max;

        return $this;
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
}
