<?php

declare(strict_types=1);

namespace Pin\Faker;

use Pin\Validation\Rules\Enum;

/**
 * FakeRule 解析器
 *
 * 从 Validation Rules 中提取 FakeRule
 */
class RuleParser
{
    /**
     * 提取 FakeRule
     */
    public function parse(RuleBag $rules): ?FakeRule
    {
        foreach ($rules->rules() as $rule) {
            $parsed = $this->parseRule($rule);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return app(InferManager::class)->infer($rules);
    }

    /**
     * 解析单个 rule
     */
    protected function parseRule(mixed $rule): ?FakeRule
    {
        // fake:xxx
        if (is_string($rule) && str_starts_with($rule, 'fake:')) {
            $rule = substr($rule, 5);
            $arguments = array_map('trim', explode(',', $rule));
            $generator = array_shift($arguments);

            return Fake::{$generator}(...$arguments);
        }

        return match (true) {
            $rule instanceof FakeRule => $rule,
            $rule instanceof Enum => Fake::enum($rule->enum),
            default => null,
        };
    }
}
