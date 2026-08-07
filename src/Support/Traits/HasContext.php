<?php

declare(strict_types=1);

namespace Pin\Support\Traits;

use Pin\Support\Context;

/**
 * Trait HasContext
 *
 * 为类提供 Context（上下文数据）的存储、读取与填充能力。
 */
trait HasContext
{
    /**
     * Context 数据容器
     */
    protected Context $context;

    /**
     * 获取、设置或替换 Context 数据。
     *
     * @param  array<string, mixed>|string|null  $key  键名、键值数组或 null
     * @param  mixed  $value  对应值；当 `$key === null` 时，表示替换整个 Context 数据
     * @return ($key is null
     *     ? ($value is null ? Context : static)
     *     : static)
     */
    public function context(array|string|null $key = null, mixed $value = null): mixed
    {
        // 延迟初始化 Context 容器
        $this->context ??= new Context();
        $numArgs = func_num_args();

        // 获取 Context 实例
        if ($numArgs === 0) {
            return $this->context;
        }

        // 获取指定 Context 值
        if ($numArgs === 1 && is_string($key)) {
            return $this->context->get($key);
        }

        // 替换整个 Context 数据
        if ($key === null) {
            $this->context = Context::new($value);
        } else {
            // 设置单个值或批量设置
            $values = is_array($key) ? $key : [$key => $value];

            foreach ($values as $key => $value) {
                $this->context->set($key, $value);
            }
        }

        return $this;
    }
}
