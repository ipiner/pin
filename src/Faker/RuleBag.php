<?php

declare(strict_types=1);

namespace Pin\Faker;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation Rule 容器
 *
 * 用于解析 Laravel Validation Rules
 */
class RuleBag
{
    /**
     * 原始 Validation Rules
     *
     * @var array<int, string>
     */
    protected array $rules;

    /**
     * 已解析的规则缓存。
     *
     * @var array<string, array<int, string>>|null
     */
    protected array $parsedRules;

    /**
     * @var string|array<int, string|ValidationRule> 原始 Validation Rules
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
        return array_key_exists($rule, $this->parsedRules());
    }

    /**
     * 是否 nullable
     */
    public function isNullable(): bool
    {
        return $this->has('nullable');
    }

    /**
     * 是否 required
     */
    public function isRequired(): bool
    {
        return $this->has('required');
    }

    /**
     * 获取规则的第一个参数。
     *
     * 示例：
     *
     * ```
     * integer|max:255
     *
     * $rules->parameter('max'); // 255
     * $rules->parameter('min'); // null
     * ```
     */
    public function parameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters($name)[0] ?? $default;
    }

    /**
     * 获取全部参数
     *
     * @return array<int, string>
     */
    public function parameters(string $name): array
    {
        return $this->parsedRules()[$name] ?? [];
    }

    /**
     * 获取原始 Validation Rules。
     *
     * @return array<int, string|ValidationRule>
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * 解析 Validation Rules。
     *
     * 仅解析 string rule
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
            // 忽略对象规则
            if (! is_string($rule)) {
                continue;
            }

            // 无参数规则
            if (! str_contains($rule, ':')) {
                $parsed[$rule] = [];

                continue;
            }

            // 解析参数规则
            [$name, $parameters] = explode(':', $rule, 2);
            $parsed[$name] = array_map(
                'trim',
                explode(',', $parameters),
            );
        }

        return $this->parsedRules = $parsed;
    }
}
