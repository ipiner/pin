<?php

declare(strict_types=1);

namespace Pin\Faker;

/**
 * FakeRule 推导管理器
 *
 * 用于根据 Validation Rules 推导 FakeRule
 */
class InferManager
{
    /**
     * 已注册的 infer 回调。
     *
     * - key: Validation Rule 名称
     * - value: FakeRule 推导回调
     *
     * @var array<string, callable(RuleBag): FakeRule>
     */
    protected array $infers = [];

    public function __construct()
    {
        $this->registerBuiltins();
    }

    /**
     * 注册 infer 规则
     */
    public function register(string $rule, callable $callback): void
    {
        $this->infers[$rule] = $callback;
    }

    /**
     * 推导 FakeRule
     */
    public function infer(RuleBag $rules): ?FakeRule
    {
        foreach ($this->infers as $name => $callback) {
            if ($rules->has($name)) {
                return $callback($rules);
            }
        }

        return null;
    }

    /**
     * 注册内置 infer 规则。
     */
    protected function registerBuiltins(): void
    {
        $this->registerInInfer();
        $this->registerIntegerInfer();
        // email 必须优先于 string 注册，否则会被 string infer 提前匹配
        $this->registerEmailInfer();
        $this->registerStringInfer();
    }

    /**
     * 注册 string infer
     */
    protected function registerStringInfer(): void
    {
        $this->register(
            'string',
            fn (RuleBag $rules) => Fake::string(
                (int) $rules->parameter('max', 16),
            ),
        );
    }

    /**
     * 注册 email infer
     */
    protected function registerEmailInfer(): void
    {
        $this->register('email', fn () => Fake::safeEmail());
    }

    /**
     * 注册 in infer
     */
    protected function registerInInfer(): void
    {
        $this->register(
            'in',
            fn (RuleBag $rules) => Fake::in(...$rules->parameters('in')),
        );
    }

    /**
     * 注册 integer infer
     */
    protected function registerIntegerInfer(): void
    {
        $this->register(
            'integer',
            fn (RuleBag $rules) => Fake::integer(
                (int) $rules->parameter('min', 1),
                (int) $rules->parameter('max', 10000),
            ),
        );
    }
}
