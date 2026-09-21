<?php

declare(strict_types=1);

namespace Pin\Support;

use BackedEnum;
use Closure;
use Illuminate\Support\Str as BaseStr;
use UnitEnum;

/**
 * 字符串工具类
 */
class Str
{
    /**
     * @var (Closure(mixed, ?string): mixed)|null
     */
    protected static ?Closure $sensitiveValueMasker = null;

    /**
     * 默认脱敏策略
     */
    public static function defaultSensitiveValueMasker(mixed $value, ?string $key): mixed
    {
        if ($key === null || stripos($key, 'password') !== false) {
            return BaseStr::limit((string) $value, 3, '******');
        }

        return $value;
    }

    /**
     * 拆分字符串，去除首尾空白和空项
     *
     * @return list<string>
     */
    public static function explode(?string $str, string $delimiter = ','): array
    {
        $str = trim($str ?? '');

        if ($str === '') {
            return [];
        }

        $result = [];

        foreach (explode($delimiter, $str) as $item) {
            $item = trim($item);

            if ($item !== '') {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * 将分隔字符串转换为整数数组
     *
     * @return list<int>
     */
    public static function explodeToIntegers(?string $ids, string $delimiter = ','): array
    {
        return array_map('intval', static::explode($ids, $delimiter));
    }

    /**
     * 替换占位符，支持 :key 或 {key}
     */
    public static function format(string $str, array $replacement, string $delimiter = '{}'): string
    {
        $prefix = $delimiter[0];
        $suffix = $delimiter[1] ?? '';

        $replace = [];

        foreach ($replacement as $key => $value) {
            $replace[$prefix.$key.$suffix] = $value;
        }

        return strtr($str, $replace);
    }

    /**
     * 校验字符串是否为合法 UTF-8
     */
    public static function isValidUtf8(string $str): bool
    {
        return preg_match('//u', $str) === 1;
    }

    /**
     * 敏感值脱敏
     */
    public static function maskSensitive(mixed $value, ?string $key = null): mixed
    {
        if (is_array($value)) {
            return Arr::maskSensitive($value);
        }

        return static::$sensitiveValueMasker
            ? (static::$sensitiveValueMasker)($value, $key)
            : static::defaultSensitiveValueMasker($value, $key);
    }

    /**
     * 设置敏感值脱敏策略
     *
     * @param  (Closure(mixed, ?string): mixed)|null  $masker
     */
    public static function setSensitiveValueMasker(?Closure $masker): void
    {
        static::$sensitiveValueMasker = $masker;
    }

    /**
     * 转换为字符串
     */
    public static function string(mixed $value): string
    {
        return match (true) {
            is_string($value) => $value,
            $value instanceof BackedEnum => (string) $value->value,
            $value instanceof UnitEnum => $value->name,
            default => (string) $value,
        };
    }
}
