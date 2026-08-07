<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Illuminate\Support\Collection;
use Pin\Errors\Errors;

/**
 * 连续重复字符验证
 *
 * 用于限制密码中连续重复字符的最大长度。
 *
 * 支持检测：
 * - 连续重复字母
 * - 连续重复数字
 * - 连续重复符号
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
        $has = collect(str_split($this->value))
            ->chunkWhile(function (string $value, string $key, Collection $chunk) {
                return $value === $chunk->last();
            })
            ->first(fn ($value) => $value->count() >= $this->maxRepeatedCharacters);

        return $has
            ? $this->addError(Errors::PasswordTooManyRepeats, ['size' => $this->maxRepeatedCharacters])
            : 0;
    }
}
