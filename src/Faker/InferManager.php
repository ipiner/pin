<?php

declare(strict_types=1);

namespace Pin\Faker;

/**
 * 生成规则推导器
 */
class InferManager
{
    /**
     * 已注册的推导回调
     *
     * @var array<string, callable(RuleBag): FakeRule>
     */
    protected array $infers = [];

    public function __construct()
    {
        $this->registerBuiltins();
    }

    /**
     * 注册规则推导器
     *
     * @param  callable(RuleBag): FakeRule  $callback
     */
    public function register(string $rule, callable $callback): void
    {
        $this->infers[$rule] = $callback;
    }

    /**
     * 推导生成规则
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
     * 注册内置推导器
     */
    protected function registerBuiltins(): void
    {
        $this->registerInInfer();
        $this->registerIntegerInfer();
        // 邮箱规则优先于字符串规则
        $this->registerEmailInfer();
        $this->registerStringInfer();
    }

    /**
     * 注册字符串推导器
     */
    protected function registerStringInfer(): void
    {
        $this->register(
            'string',
            static fn (RuleBag $rules) => Fake::string(
                (int) $rules->parameter('max', max(16, (int) $rules->parameter('min', 0))),
            ),
        );
    }

    /**
     * 注册邮箱推导器
     */
    protected function registerEmailInfer(): void
    {
        $this->register('email', static fn () => Fake::safeEmail());
    }

    /**
     * 注册候选值推导器
     */
    protected function registerInInfer(): void
    {
        $this->register(
            'in',
            static fn (RuleBag $rules) => Fake::in(...$rules->parameters('in')),
        );
    }

    /**
     * 注册整数推导器
     */
    protected function registerIntegerInfer(): void
    {
        $this->register('integer', static function (RuleBag $rules) {
            $min = $rules->parameter('min');
            $max = $rules->parameter('max');

            return Fake::integer(
                (int) ($min ?? min(1, $max ?? 1)),
                (int) ($max ?? max(10000, $min ?? 10000)),
            );
        });
    }
}
