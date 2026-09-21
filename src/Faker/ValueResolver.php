<?php

declare(strict_types=1);

namespace Pin\Faker;

use Closure;
use Illuminate\Support\Str;
use Pin\Faker\Generators\Generator;

/**
 * 生成规则值解析器
 */
class ValueResolver
{
    /**
     * 内置生成器类缓存
     *
     * @var array<string, class-string<Generator>|null>
     */
    protected array $generators = [];

    /**
     * 解析生成规则
     */
    public function resolve(FakeRule $rule, ?RuleBag $rules = null): mixed
    {
        $generator = $rule->generator();

        if ($generator instanceof Closure) {
            return $generator($rules ?? new RuleBag([]), ...$rule->parameters());
        }

        if ($class = $this->resolveBuiltinGenerator($generator)) {
            return app($class)->generate($rule, $rules);
        }

        return fake()->{$generator}(...$rule->parameters());
    }

    /**
     * 解析内置生成器类
     *
     * @return class-string<Generator>|null
     */
    protected function resolveBuiltinGenerator(string $generator): ?string
    {
        if (array_key_exists($generator, $this->generators)) {
            return $this->generators[$generator];
        }

        $class = __NAMESPACE__.'\\Generators\\'.Str::studly($generator).'Generator';

        return $this->generators[$generator] = class_exists($class) ? $class : null;
    }
}
