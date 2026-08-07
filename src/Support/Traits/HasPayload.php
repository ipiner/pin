<?php

declare(strict_types=1);

namespace Pin\Support\Traits;

use Illuminate\Support\Arr;

/**
 * Trait HasPayload
 *
 * 为类提供 payload 数据的存储、读取与更新能力。
 */
trait HasPayload
{
    /**
     * Payload 数据
     */
    protected array $payload = [];

    /**
     * 获取、设置或替换 payload 数据
     *
     * @param  array<string, mixed>|string|null  $key  键名、键值数组或 null
     * @param  mixed  $value  对应值；当 `$key === null` 时，表示替换整个 payload 数据
     * @return ($key is null ? array : ($key is string ? ($value is null ? mixed : static) : static)
     */
    public function payload(array|string|null $key = null, mixed $value = null): mixed
    {
        $numArgs = func_num_args();

        // 获取整个 payload
        if ($numArgs === 0) {
            return $this->payload;
        }

        // 获取指定 payload 值
        if ($numArgs === 1 && is_string($key)) {
            return Arr::get($this->payload, $key);
        }

        // 替换整个 payload
        if ($key === null) {
            $this->payload = $value;
        } else {
            // 设置单个值或批量设置
            $values = is_array($key) ? $key : [$key => $value];

            foreach ($values as $key => $value) {
                Arr::set($this->payload, $key, $value);
            }
        }

        return $this;
    }
}
