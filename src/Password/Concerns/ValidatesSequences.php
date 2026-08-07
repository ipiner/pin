<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Illuminate\Support\Collection;
use Pin\Errors\Errors;

/**
 * 连续顺序字符验证
 *
 * 用于限制密码中连续递增或递减的字符序列长度。
 *
 * 支持检测：
 * - 连续递增字符
 * - 连续递减字符
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
        // 递增字符：
        // abc / 123
        $has = collect(str_split($this->value))
            ->chunkWhile(function (string $value, string $key, Collection $chunk) {
                return ord($value) === ord((string) $chunk->last()) + 1;
            })
            ->first(fn ($value) => $value->count() >= $this->maxSequentialCharacters);

        // 递减字符：
        // cba / 321
        if (! $has) {
            $has = collect(str_split($this->value))
                ->chunkWhile(function (string $value, string $key, Collection $chunk) {
                    return ord($value) === ord((string) $chunk->last()) - 1;
                })
                ->first(fn ($value) => $value->count() >= $this->maxSequentialCharacters);
        }

        return $has
            ? $this->addError(Errors::PasswordSequenceTooLong, ['size' => $this->maxSequentialCharacters])
            : 0;
    }
}
