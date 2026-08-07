<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Pin\Faker\FakeRule;
use Pin\Faker\RuleBag;

/**
 * Fake Generator 基类
 *
 * 所有 Fake 数据生成器的抽象实现：
 * - 规则驱动生成
 * - 支持 nullable 控制
 * - 统一生成生命周期
 */
abstract class Generator
{
    /**
     * 当前 FakeRule
     */
    protected FakeRule $rule;

    /**
     * 当前字段的 Validation Rules
     */
    protected RuleBag $rules;

    /**
     * 生成 fake 数据
     */
    abstract public function fake();

    /**
     * 创建 Generator 实例
     */
    public static function make(string $name): static
    {
        $class = sprintf(
            '%s\\%sGenerator',
            __NAMESPACE__,
            $name
        );

        return app($class);
    }

    /**
     * 执行生成流程
     */
    public function generate(FakeRule $rule, ?RuleBag $rules = null): mixed
    {
        $this->rule = $rule;
        $this->rules = $rules ?? new RuleBag([]);

        // nullable 随机返回 null
        if ($this->shouldReturnNull()) {
            return null;
        }

        return $this->fake();
    }

    /**
     * 是否返回 null
     */
    protected function shouldReturnNull(): bool
    {
        if (! $this->rules->isNullable()) {
            return false;
        }

        // 默认 20% 概率
        $chance = (int) ($this->rules->parameter('nullable', 20));

        return random_int(1, 100) <= $chance;
    }
}
