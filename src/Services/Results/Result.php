<?php

declare(strict_types=1);

namespace Pin\Services\Results;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * 操作结果
 *
 * 用于定义 Service 层统一结果对象规范
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
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
