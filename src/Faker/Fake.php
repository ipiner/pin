<?php

declare(strict_types=1);

namespace Pin\Faker;

use Closure;
use Faker\Generator;
use Illuminate\Support\Traits\Macroable;

/**
 * Fake DSL
 *
 * 提供 fake 数据生成规则定义：
 * - FakerPHP generators
 * - Closure generators
 * - Rule inference
 * - Macro extensions
 *
 * @mixin Generator
 *
 * @method static FakeRule infer() 根据 Validation Rules 自动推导 FakeRule
 * @method static FakeRule string(int $length = 16) 生成随机字符串
 * @method static FakeRule integer(int $min = 1, int $max = 10000) 生成随机整数
 * @method static FakeRule password(string $plain = 'test@123') 生成密码，适用于请求传输的格式（如前端提交前处理）
 * @method static FakeRule in(mixed ...$value) 从给定值中随机选择
 * @method static FakeRule enum(string $enum) 从枚举中随机选择
 */
class Fake
{
    use Macroable {
        __callStatic as macroCall;
    }

    /**
     * 创建 FakeRule
     */
    public static function __callStatic($method, $parameters): FakeRule
    {
        if (static::hasMacro($method)) {
            return static::macroCall($method, $parameters);
        }

        return new FakeRule($method, $parameters);
    }

    /**
     * 根据规则批量生成 fake 数据。
     *
     * @param  array  $rules  Validation Rules + FakeRule
     * @return array<string, mixed>
     */
    public static function generate(array $rules): array
    {
        return app(Faker::class)->generate($rules);
    }

    /**
     * 创建 FakeRule 实例。
     */
    public static function make(string|Closure $generator, array $arguments = []): FakeRule
    {
        return new FakeRule($generator, $arguments);
    }

    /**
     * 注册规则推导器
     */
    public static function registerInfer(string $rule, callable $callback): void
    {
        app(InferManager::class)->register($rule, $callback);
    }
}
