<?php

declare(strict_types=1);

namespace Pin\Errors;

/**
 * 错误注册中心（Error Registry）
 *
 * 用于统一管理系统中所有 IError 实例：
 * - 根据错误码解析错误定义
 * - 提供全局错误查找能力
 * - 支持错误枚举批量注册
 *
 * 作为错误系统的运行时索引容器
 */
class Registry
{
    /**
     * 已注册错误集合
     *
     * @var array<int, IError>
     */
    protected static array $errors = [];

    /**
     * 获取所有已注册错误
     *
     * @return IError[]
     */
    public static function all(): array
    {
        return static::$errors;
    }

    /**
     * 根据错误码获取错误定义
     *
     *  未命中时返回 Unknown
     */
    public static function get(int $code): IError
    {
        $errors = static::all();

        return $errors[$code]
            ?? $errors[Errors::Unknown->code()]
            ?? Errors::Unknown;
    }

    /**
     * 自动加载错误枚举
     *
     * 扫描指定命名空间下的 enum 并注册其 cases
     */
    public static function load(string $path, string $namespace = 'App\\Errors'): bool
    {
        if (! is_dir($path)) {
            return false;
        }

        foreach (scandir($path) as $item) {
            if (
                str_ends_with($item, '.php')
                && enum_exists($enum = $namespace.'\\'.basename($item, '.php'))
            ) {
                /** @var IError $enum */
                self::register($enum::cases());
            }
        }

        return true;
    }

    /**
     * 批量注册错误定义
     *
     * @param  IError[]  $cases
     */
    public static function register(array $cases): void
    {
        foreach ($cases as $item) {
            /**
             * 以 code 为 key 存储
             * 后注册的会覆盖前面的（允许扩展或重写错误定义）
             */
            static::$errors[$item->code()] = $item;
        }
    }
}
