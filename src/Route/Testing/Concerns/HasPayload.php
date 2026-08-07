<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

/**
 * Payload 支持
 */
trait HasPayload
{
    /**
     * 当前请求的 payload 数据
     *
     * @var array<string, mixed>|null
     */
    protected ?array $payload = null;

    /**
     * 设置请求 payload
     *
     * @param  array<string, mixed>  $payload
     */
    public function withPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * 使用 Action::fakeData 生成假数据并设置为 payload
     *
     * @param  array<string, mixed>  $attributes  用于覆盖默认 fake 数据的属性
     */
    public function fakePayload(array $attributes = []): static
    {
        return $this->withPayload($this->action()->fakeData($attributes));
    }
}
