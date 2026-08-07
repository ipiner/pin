<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Assertions;

use Closure;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * 响应消息断言支持。
 *
 * 为 TestResponse 提供统一的 `message` 字段断言能力，用于验证 API 响应中的业务消息。
 */
trait AssertMessage
{
    /**
     * 响应消息完全匹配断言
     */
    public function assertMessage(string $message): static
    {
        return $this->assertMessageUsing(fn (string $s) => $s === $message);
    }

    /**
     * 响应消息包含断言。
     *
     * 断言 JSON `message` 字段包含指定字符串。
     */
    public function assertMessageContains(string $message, bool $caseSensitive = true): static
    {
        return $this->assertMessageUsing(
            fn (string $actual) => $caseSensitive
                ? str_contains($actual, $message)
                : str_contains(strtolower($actual), strtolower($message))
        );
    }

    /**
     * 响应消息正则匹配断言。
     *
     * 使用正则表达式断言 JSON `message` 字段。
     */
    public function assertMessageMatch(string $pattern): static
    {
        return $this->assertMessageUsing(
            fn (string $actual) => preg_match($pattern, $actual) === 1
        );
    }

    /**
     * 自定义响应消息断言。
     *
     * 允许通过 Closure 自定义 `message` 字段断言逻辑。
     *
     * @param  Closure(string): bool  $using
     */
    public function assertMessageUsing(Closure $using): static
    {
        $this->response->assertJson(
            fn (AssertableJson $json) => $json->where('message', $using)->etc()
        );

        return $this;
    }
}
