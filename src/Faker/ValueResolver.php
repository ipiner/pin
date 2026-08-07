<?php

declare(strict_types=1);

namespace Pin\Faker;

use Closure;
use Illuminate\Support\Str;
use Pin\Faker\Generators\Generator;

/**
 * FakeRule 值解析器
 *
 * 将 FakeRule 转换为最终 fake 值
 */
class ValueResolver
{
    /**
     * 解析 FakeRule
     */
    public function resolve(FakeRule $rule, ?RuleBag $rules = null): mixed
    {
        $rules ??= new RuleBag([]);

        // closure
        if ($rule->generator() instanceof Closure) {
            return ($rule->generator())($rules, ...$rule->parameters());
        }

        // 内置
        if ($class = $this->resolveBuiltinGenerator($rule->generator())) {
            return app($class)->generate($rule, $rules);
        }

        // FakerPHP
        return fake()->{$rule->generator()}(...$rule->parameters());
    }

    /**
     * 解析内置 Generator
     */
    protected function resolveBuiltinGenerator(string $generator): ?string
    {
        $class = sprintf(
            'Pin\\Faker\\Generators\\%sGenerator',
            Str::studly($generator),
        );

        return class_exists($class) ? $class : null;
    }
}
