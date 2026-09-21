<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\Fluent;
use Override;
use RuntimeException;

/**
 * 支持严格读取的数据容器
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
     * 创建或复用数据容器
     */
    public static function new($context): static
    {
        if ($context instanceof static) {
            return $context;
        }

        if (is_object($context) && method_exists($context, 'toArray')) {
            $context = $context->toArray();
        }

        return new static($context ?? []);
    }

    /**
     * 获取值
     */
    #[Override]
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
