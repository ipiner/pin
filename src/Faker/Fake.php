<?php

declare(strict_types=1);

namespace Pin\Faker;

use Closure;
use Faker\Generator;
use Illuminate\Support\Traits\Macroable;

/**
 * 测试数据规则入口
 *
 * @mixin Generator
 *
 * @method static FakeRule infer() 根据验证规则推导生成器
 * @method static FakeRule string(int $length = 16) 生成随机字符串
 * @method static FakeRule integer(int $min = 1, int $max = 10000) 生成随机整数
 * @method static FakeRule password(string $plain = 'test@123') 生成请求传输密码
 * @method static FakeRule in(mixed ...$value) 从给定值中随机选择
 * @method static FakeRule enum(string $enum) 从枚举中随机选择
 */
class Fake
{
    use Macroable {
        __callStatic as macroCall;
    }

    /**
     * 创建生成规则
     */
    public static function __callStatic($method, $parameters): FakeRule
    {
        if (static::hasMacro($method)) {
            return static::macroCall($method, $parameters);
        }

        return new FakeRule($method, $parameters);
    }

    /**
     * 根据验证规则生成测试数据
     *
     * @param  array<string, array|string>  $rules  验证规则
     * @return array<string, mixed>
     */
    public static function generate(array $rules): array
    {
        return app(Faker::class)->generate($rules);
    }

    /**
     * 创建生成规则
     */
    public static function make(string|Closure $generator, array $arguments = []): FakeRule
    {
        return new FakeRule($generator, $arguments);
    }

    /**
     * 注册规则推导器
     *
     * @param  callable(RuleBag): FakeRule  $callback
     */
    public static function registerInfer(string $rule, callable $callback): void
    {
        app(InferManager::class)->register($rule, $callback);
    }
}
