<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * 数据大小处理工具类
 */
class Size
{
    /**
     * 1 KB（1024 字节）
     */
    public const int K = 1024;

    /**
     * 1 MB（1024 * 1024）
     */
    public const int M = 1048576;

    /**
     * 1 GB（1024 * 1024 * 1024）
     */
    public const int G = 1073741824;

    /**
     * 将字节数格式化为可读字符串
     *
     * @param  int  $bytes  字节数
     */
    public static function format(int $bytes): string
    {
        return match (true) {
            $bytes >= static::G => round($bytes / static::G, 2).'G',
            $bytes >= static::M => round($bytes / static::M, 2).'M',
            $bytes >= static::K => round($bytes / static::K, 2).'K',
            default => $bytes.'B'
        };
    }

    /**
     * 将带单位的字符串转换为字节数
     *
     * @throws InvalidArgumentException 当单位不合法时抛出异常
     */
    public static function toBytes(string $value): int
    {
        $value = strtolower($value);

        // 提取数值部分（支持浮点）
        $size = (float) $value;

        return (int) match (true) {
            Str::endsWith($value, ['k', 'kb']) => $size * static::K,
            Str::endsWith($value, ['m', 'mb']) => $size * static::M,
            Str::endsWith($value, ['g', 'gb']) => $size * static::G,
            Str::endsWith($value, 'b') => $size,
            default => throw new InvalidArgumentException('Invalid size suffix'),
        };
    }
}
