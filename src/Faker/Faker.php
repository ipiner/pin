<?php

declare(strict_types=1);

namespace Pin\Faker;

/**
 * Fake 数据生成器
 *
 * 基于 Validation Rules 生成 fake 数据：
 * - FakeRule parsing
 * - Rule inference
 * - Generator resolving
 */
class Faker
{
    /**
     * 创建 Faker 实例
     */
    public function __construct(
        public protected(set) RuleParser $ruleParser,
        public protected(set) ValueResolver $valueResolver
    ) {
        //
    }

    /**
     * 根据 Validation Rules 生成 fake 数据。
     *
     * @return array<string, mixed>
     */
    public function generate(array $rules): array
    {
        $data = [];

        foreach ($rules as $field => $item) {
            $bag = $this->normalize($item);
            $rule = $this->ruleParser->parse($bag);

            if ($rule === null || $this->isWildcardField($field)) {
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
     * 判断字段是否为 Laravel wildcard 字段
     *
     *  例如：
     *  - users.*.id
     *  - items.*.name
     */
    protected function isWildcardField(string $field): bool
    {
        return str_contains($field, '*');
    }

    /**
     * 标准化 Validation Rules
     */
    protected function normalize(array|string $rules): RuleBag
    {
        return new RuleBag(
            is_array($rules) ? $rules : explode('|', $rules)
        );
    }
}
