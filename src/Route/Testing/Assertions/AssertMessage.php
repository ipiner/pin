<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Assertions;

use Closure;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * 响应消息断言
 */
trait AssertMessage
{
    /**
     * 响应消息完全匹配断言
     */
    public function assertMessage(string $message): static
    {
        return $this->assertMessageUsing(static fn (string $actual) => $actual === $message);
    }

    /**
     * 响应消息包含断言
     */
    public function assertMessageContains(string $message, bool $caseSensitive = true): static
    {
        return $this->assertMessageUsing(
            static fn (string $actual) => $caseSensitive
                ? str_contains($actual, $message)
                : stripos($actual, $message) !== false
        );
    }

    /**
     * 响应消息正则匹配断言
     */
    public function assertMessageMatch(string $pattern): static
    {
        return $this->assertMessageUsing(
            static fn (string $actual) => preg_match($pattern, $actual) === 1
        );
    }

    /**
     * 自定义响应消息断言
     *
     * @param  Closure(string): bool  $using
     */
    public function assertMessageUsing(Closure $using): static
    {
        $this->response->assertJson(
            static fn (AssertableJson $json) => $json->where('message', $using)->etc()
        );

        return $this;
    }
}
