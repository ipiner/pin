<?php

declare(strict_types=1);

namespace Pin\Attributes;

use Pin\Support\Facades\RuntimeCache;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionEnumUnitCase;
use UnitEnum;

/**
 * Attribute 解析
 */
class Attribute
{
    /**
     * 获取目标对象上的 Attribute
     *
     * @template TAttribute
     *
     * @param  class-string<TAttribute>  $attribute
     * @return TAttribute|null
     */
    public static function get(UnitEnum|string $target, string $attribute)
    {
        if (is_string($target)) {
            return static::fromClass($target, $attribute);
        }

        return static::fromCase($target, $attribute);
    }

    /**
     * 获取枚举 case 上的 Attribute。
     */
    protected static function fromCase(
        UnitEnum $case,
        string $attribute
    ): mixed {
        $enum = get_class($case);
        $name = $case->name;

        return RuntimeCache::rememberForever(
            "{$enum}.{$name}.{$attribute}",
            fn () => static::resolve(
                new ReflectionEnumUnitCase($enum, $name),
                $attribute
            )
        ) ?: null;
    }

    /**
     * 获取类上的 Attribute
     */
    protected static function fromClass(string $target, string $attribute): mixed
    {
        return RuntimeCache::rememberForever(
            $target.'.'.$attribute,
            fn () => static::resolve(
                new ReflectionClass($target),
                $attribute
            )
        ) ?: null;
    }

    /**
     * 从 Reflection 对象中创建 Attribute 实例。
     */
    protected static function resolve(
        ReflectionClass|ReflectionClassConstant $reflection,
        string $class
    ): mixed {
        $attributes = $reflection->getAttributes($class);

        if ($attributes === []) {
            return false; // 使用 false 缓存
        }

        return $attributes[0]->newInstance();
    }
}
