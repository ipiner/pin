<?php

declare(strict_types=1);

namespace Pin\Errors;

/**
 * 错误注册表
 */
class Registry
{
    /**
     * 已注册错误
     *
     * @var array<int, IError>
     */
    protected static array $errors = [];

    /**
     * 获取所有错误
     *
     * @return array<int, IError>
     */
    public static function all(): array
    {
        return static::$errors;
    }

    /**
     * 查找错误，未注册时返回未知错误
     */
    public static function get(int $code): IError
    {
        return static::$errors[$code] ?? static::resolve(Errors::Unknown);
    }

    /**
     * 加载目录中的错误枚举
     */
    public static function load(string $path, string $namespace = 'App\\Errors'): bool
    {
        if (! is_dir($path)) {
            return false;
        }

        foreach (scandir($path) as $item) {
            if (! str_ends_with($item, '.php')) {
                continue;
            }

            $enum = $namespace.'\\'.basename($item, '.php');

            if (enum_exists($enum) && is_subclass_of($enum, IError::class)) {
                static::register($enum::cases());
            }
        }

        return true;
    }

    /**
     * 注册错误，同码覆盖
     *
     * @param  IError[]  $cases
     */
    public static function register(array $cases): void
    {
        foreach ($cases as $case) {
            static::$errors[$case->code()] = $case;
        }
    }

    /**
     * 获取覆盖后的错误定义
     */
    public static function resolve(IError $error): IError
    {
        return static::$errors[$error->code()] ?? $error;
    }
}
