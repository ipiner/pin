<?php

declare(strict_types=1);

namespace Pin\Support\Traits;

use Illuminate\Support\Arr;

/**
 * Payload 数据读写
 */
trait HasPayload
{
    /**
     * Payload 数据
     */
    protected array $payload = [];

    /**
     * 获取、设置或替换 payload
     *
     * @param  array<string, mixed>|string|null  $key
     */
    public function payload(array|string|null $key = null, mixed $value = null): mixed
    {
        $numArgs = func_num_args();

        if ($numArgs === 0) {
            return $this->payload;
        }

        if ($numArgs === 1 && is_string($key)) {
            return Arr::get($this->payload, $key);
        }

        if ($key === null) {
            $this->payload = $value ?? [];
        } else {
            $values = is_array($key) ? $key : [$key => $value];

            foreach ($values as $key => $value) {
                Arr::set($this->payload, $key, $value);
            }
        }

        $this->payloadChanged();

        return $this;
    }

    /**
     * 处理 payload 更新
     */
    protected function payloadChanged(): void
    {
    }
}
