<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\Fluent;
use RuntimeException;

/**
 * 轻量级数据容器
 *
 * 继承于 `Laravel\Support\Fluent` 实现，在获取数据时，严格模式（不存在 key ）下会抛异常
 */
class DataBag extends Fluent
{
    /**
     * @param  iterable<string, mixed>  $attributes  存储的数据
     * @param  bool  $strict  严格模式
     */
    public function __construct($attributes = [], protected bool $strict = true)
    {
        parent::__construct($attributes);
    }

    /**
     * 统一解析输入类型
     *
     * @param  mixed  $context  上下文输入
     */
    public static function new($context): static
    {
        return match (true) {
            $context === null => new static(),
            is_array($context) => new static($context),
            ! $context instanceof static => new static($context->toArray()),
            default => $context
        };
    }

    /**
     * 获取值
     */
    public function value($key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if ($this->strict) {
            throw new RuntimeException(sprintf('Undefined array key "%s"', $key));
        }

        return value($default);
    }
}
