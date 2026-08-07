<?php

declare(strict_types=1);

namespace Pin\Support;

use BackedEnum;
use Closure;
use UnitEnum;

/**
 * 字符串工具类
 */
class Str
{
    /**
     * 自定义敏感值脱敏处理器（masking resolver）
     *
     * 策略函数签名
     * ```
     * function (mixed $value, ?string $key): mixed
     * ```
     */
    protected static ?Closure $sensitiveValueMasker = null;

    /**
     * 默认敏感值脱敏处理器
     *
     * @param  mixed  $value  待处理的原始值
     * @param  string|null  $key  字段名称，用于判断是否为敏感字段，`null` 时视为脱敏
     */
    public static function defaultSensitiveValueMasker(mixed $value, ?string $key): mixed
    {
        if ($key === null || stripos($key, 'password') !== false) {
            return \Illuminate\Support\Str::limit((string) $value, 3, '******');
        }

        return $value;
    }

    /**
     * 将字符串按分隔符拆分为数组，并去除空值与空格
     *
     * @param  string|null  $str  原始字符串
     * @param  string  $delimiter  分隔符
     * @return string[] 清洗后的字符串数组
     */
    public static function explode(?string $str, string $delimiter = ','): array
    {
        if ($str === null) {
            return [];
        }

        $str = trim($str);
        if ($str === '') {
            return [];
        }

        $result = [];
        foreach (explode($delimiter, $str) as $item) {
            if ('' !== ($item = trim($item))) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * 将分隔字符串转换为整数数组
     *
     * @return int[]
     */
    public static function explodeToIntegers(?string $ids, string $delimiter = ','): array
    {
        return array_map('intval', static::explode($ids, $delimiter));
    }

    /**
     * 字符串模板格式化（简单 placeholder 替换）
     *
     * 占位符规则：
     * - 单个字符：`: => :key`
     * - 双字符：`{} => {key}`
     */
    public static function format(string $str, array $replacement, string $delimiter = '{}'): string
    {
        $ldelim = $delimiter[0];
        $rdelim = $delimiter[1] ?? '';

        $replace = [];
        foreach ($replacement as $key => $value) {
            $replace[$ldelim.$key.$rdelim] = $value;
        }

        return strtr($str, $replace);
    }

    /**
     * 校验字符串是否为合法 UTF-8
     */
    public static function isValidUtf8(string $str): bool
    {
        json_encode($str);

        return json_last_error() !== JSON_ERROR_UTF8;
    }

    /**
     * 敏感信息脱敏处理
     *
     *
     * @param  mixed  $value  任意值
     * @param  string|null  $key  字段名（用于判断敏感字段）
     * @return mixed 脱敏后的值
     */
    public static function maskSensitive(mixed $value, ?string $key = null): mixed
    {
        if (is_array($value)) {
            return Arr::maskSensitive($value);
        }

        $masker = static::$sensitiveValueMasker ?? null;

        return $masker
            ? $masker($value, $key)
            : static::defaultSensitiveValueMasker($value, $key);
    }

    /**
     * 设置敏感值脱敏策略
     *
     * @param  Closure  $masker  `function (mixed $value, ?string $key): mixed`
     */
    public static function setSensitiveValueMasker(?Closure $masker): void
    {
        static::$sensitiveValueMasker = $masker;
    }

    /**
     * 将任意值统一转换为字符串表示
     */
    public static function string(mixed $value): string
    {
        return match (true) {
            is_string($value) => $value,
            $value instanceof BackedEnum => $value->value, // enum Name: string
            $value instanceof UnitEnum => $value->name, // enum Name
            default => (string) $value,
        };
    }
}
