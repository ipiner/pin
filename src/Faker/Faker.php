<?php

declare(strict_types=1);

namespace Pin\Faker;

/**
 * 根据验证规则生成测试数据
 */
class Faker
{
    /**
     * 构造函数
     */
    public function __construct(
        public protected(set) RuleParser $ruleParser,
        public protected(set) ValueResolver $valueResolver
    ) {
    }

    /**
     * 生成测试数据。
     *
     * @return array<string, mixed>
     */
    public function generate(array $rules): array
    {
        $data = [];

        foreach ($rules as $field => $fieldRules) {
            if ($this->isWildcardField($field)) {
                continue;
            }

            $bag = $this->normalize($fieldRules);
            $rule = $this->ruleParser->parse($bag);

            if (! $rule) {
                continue;
            }

            $value = $this->valueResolver->resolve($rule, $bag);

            if (! $value instanceof MissingValue) {
                $data[$field] = $value;
            }
        }

        return $data;
    }

    /**
     * 是否为通配符字段
     */
    protected function isWildcardField(string $field): bool
    {
        return str_contains($field, '*');
    }

    /**
     * 标准化验证规则
     */
    protected function normalize(array|string $rules): RuleBag
    {
        return new RuleBag($rules);
    }
}
