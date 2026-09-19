<?php

declare(strict_types=1);

namespace Pin\Faker;

use Pin\Validation\Rules\Enum;

/**
 * 生成规则解析器
 */
class RuleParser
{
    /**
     * 提取或推导生成规则
     */
    public function parse(RuleBag $rules): ?FakeRule
    {
        foreach ($rules->rules() as $rule) {
            $parsed = $this->parseRule($rule);
            if (! $parsed) {
                continue;
            }

            if ($parsed->generator() !== 'infer') {
                return $parsed;
            }

            break;
        }

        return app(InferManager::class)->infer($rules);
    }

    /**
     * 解析单条规则
     */
    protected function parseRule(mixed $rule): ?FakeRule
    {
        if (is_string($rule) && str_starts_with($rule, 'fake:')) {
            $arguments = array_map('trim', explode(',', substr($rule, 5)));
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
