<?php

declare(strict_types=1);

namespace Pin\Models\Cache;

/**
 * 空值占位符（用于缓存穿透保护）
 *
 * 存储格式：
 * {expiredAt}__cache:null_placeholder__
 */
class NullPlaceholder
{
    /**
     * 占位符后缀（用于标识该缓存值为“空值”）
     */
    protected const string VALUE = '__cache:null_placeholder__';

    /**
     * 过期时间（时间戳）
     */
    public function __construct(protected int $expiredAt)
    {
    }

    /**
     * 判断是否为占位符缓存值
     *
     * @param  mixed  $value  缓存中的原始值
     */
    public static function isHolderValue(mixed $value): bool
    {
        return is_string($value) && str_ends_with($value, static::VALUE);
    }

    /**
     * 创建一个空值占位符实例
     */
    public static function make(int $ttl = 3600): static
    {
        return new static(now()->getTimestamp() + $ttl);
    }

    /**
     * 从缓存值解析占位对象
     */
    public static function parse(mixed $value): ?static
    {
        return static::isHolderValue($value)
            ? new static((int) substr($value, 0, 10))
            : null;
    }

    /**
     * 判断占位符是否已过期
     */
    public function isExpired(): bool
    {
        return now()->getTimestamp() > $this->expiredAt;
    }

    /**
     * 转换为可存储的字符串形式
     */
    public function toString(): string
    {
        return sprintf('%d%s', $this->expiredAt, static::VALUE);
    }
}
