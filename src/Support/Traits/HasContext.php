<?php

declare(strict_types=1);

namespace Pin\Support\Traits;

use Pin\Support\Context;

/**
 * 上下文数据读写。
 */
trait HasContext
{
    /**
     * Context 数据容器
     */
    protected Context $context;

    /**
     * 获取、设置或替换上下文。
     *
     * @param  array<string, mixed>|string|null  $key
     */
    public function context(array|string|null $key = null, mixed $value = null): mixed
    {
        $numArgs = func_num_args();

        if ($numArgs > 0 && $key === null) {
            $this->context = Context::new($value);

            return $this;
        }

        $this->context ??= new Context();

        if ($numArgs === 0) {
            return $this->context;
        }

        if ($numArgs === 1 && is_string($key)) {
            return $this->context->get($key);
        }

        $values = is_array($key) ? $key : [$key => $value];

        foreach ($values as $key => $value) {
            $this->context->set($key, $value);
        }

        return $this;
    }
}
