<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Pin\Faker\FakeRule;
use Pin\Faker\RuleBag;

/**
 * 测试数据生成器
 */
abstract class Generator
{
    /**
     * 当前生成规则
     */
    protected FakeRule $rule;

    /**
     * 当前字段的验证规则
     */
    protected RuleBag $rules;

    /**
     * 生成数据
     */
    abstract public function fake();

    /**
     * 创建生成器
     */
    public static function make(string $name): static
    {
        return app(__NAMESPACE__.'\\'.$name.'Generator');
    }

    /**
     * 生成字段值
     */
    public function generate(FakeRule $rule, ?RuleBag $rules = null): mixed
    {
        $this->rule = $rule;
        $this->rules = $rules ?? new RuleBag([]);

        if ($this->shouldReturnNull()) {
            return null;
        }

        return $this->fake();
    }

    /**
     * 是否返回空值
     */
    protected function shouldReturnNull(): bool
    {
        if (! $this->rules->isNullable()) {
            return false;
        }

        $chance = (int) $this->rules->parameter('nullable', 20);

        return $chance >= 100 || ($chance > 0 && random_int(1, 100) <= $chance);
    }
}
