<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Override;

/**
 * 操作结果
 */
abstract class Result implements Arrayable, JsonSerializable
{
    /**
     * 响应消息
     */
    abstract public function message(): string;

    /**
     * JSON 序列化
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
