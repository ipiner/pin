<?php

declare(strict_types=1);

namespace Pin\Faker;

/**
 * 验证规则容器
 */
class RuleBag
{
    /**
     * 原始验证规则
     *
     * @var array<int, mixed>
     */
    protected array $rules;

    /**
     * 已解析的规则缓存
     *
     * @var array<string, array<int, string>>
     */
    protected array $parsedRules;

    /**
     * @param  string|array<int, mixed>  $rules  验证规则
     */
    public function __construct(string|array $rules)
    {
        $this->rules = is_array($rules) ? $rules : explode('|', $rules);
    }

    /**
     * 是否存在规则
     */
    public function has(string $rule): bool
    {
        return isset($this->parsedRules()[$rule]);
    }

    /**
     * 是否允许空值
     */
    public function isNullable(): bool
    {
        return $this->has('nullable');
    }

    /**
     * 是否必填
     */
    public function isRequired(): bool
    {
        return $this->has('required');
    }

    /**
     * 获取规则的第一个参数
     */
    public function parameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters($name)[0] ?? $default;
    }

    /**
     * 获取规则的全部参数
     *
     * @return array<int, string>
     */
    public function parameters(string $name): array
    {
        return $this->parsedRules()[$name] ?? [];
    }

    /**
     * 获取原始验证规则
     *
     * @return array<int, mixed>
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * 解析字符串规则
     *
     * @return array<string, array<int, string>>
     */
    protected function parsedRules(): array
    {
        if (isset($this->parsedRules)) {
            return $this->parsedRules;
        }

        $parsed = [];

        foreach ($this->rules as $rule) {
            if (! is_string($rule)) {
                continue;
            }

            if (! str_contains($rule, ':')) {
                $parsed[$rule] = [];

                continue;
            }

            [$name, $parameters] = explode(':', $rule, 2);
            $parsed[$name] = array_map('trim', explode(',', $parameters));
        }

        return $this->parsedRules = $parsed;
    }
}
