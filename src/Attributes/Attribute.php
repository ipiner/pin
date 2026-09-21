<?php

declare(strict_types=1);

namespace Pin\Attributes;

use Closure;
use Pin\Support\Facades\RuntimeCache;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionEnumUnitCase;
use UnitEnum;

/**
 * 读取类或枚举 case 上的 Attribute，并在运行时缓存中复用解析结果
 */
class Attribute
{
    /**
     * 按属性类名精确匹配，返回首个实例；不自动查找父类上的属性
     *
     * @template TAttribute of object
     *
     * @param  UnitEnum|class-string  $target
     * @param  class-string<TAttribute>  $attribute
     * @return TAttribute|null
     */
    public static function get(UnitEnum|string $target, string $attribute): ?object
    {
        if (is_string($target)) {
            return static::fromClass($target, $attribute);
        }

        return static::fromCase($target, $attribute);
    }

    /**
     * 获取枚举 case 上的 Attribute
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $attribute
     * @return TAttribute|null
     */
    protected static function fromCase(
        UnitEnum $case,
        string $attribute
    ): ?object {
        $enum = $case::class;
        $name = $case->name;

        return static::remember(
            "{$enum}.{$name}.{$attribute}",
            static fn () => static::resolve(
                new ReflectionEnumUnitCase($enum, $name),
                $attribute
            )
        );
    }

    /**
     * 获取类上的 Attribute
     *
     * @template TAttribute of object
     *
     * @param  class-string  $target
     * @param  class-string<TAttribute>  $attribute
     * @return TAttribute|null
     */
    protected static function fromClass(string $target, string $attribute): ?object
    {
        return static::remember(
            $target.'.'.$attribute,
            static fn () => static::resolve(
                new ReflectionClass($target),
                $attribute
            )
        );
    }

    /**
     * 缺失结果以 false 缓存，避免 RuntimeCache 将 null 视为未命中而重复反射
     *
     * @template TAttribute of object
     *
     * @param  Closure(): (TAttribute|false)  $resolve
     * @return TAttribute|null
     */
    protected static function remember(string $key, Closure $resolve): ?object
    {
        $attribute = RuntimeCache::rememberForever($key, $resolve);

        return $attribute === false ? null : $attribute;
    }

    /**
     * 从 Reflection 对象中创建 Attribute 实例
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $class
     * @return TAttribute|false
     */
    protected static function resolve(
        ReflectionClass|ReflectionClassConstant $reflection,
        string $class
    ): object|false {
        $attributes = $reflection->getAttributes($class);

        if ($attributes === []) {
            return false;
        }

        return $attributes[0]->newInstance();
    }
}
