<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Testing\TestResponse as BaseResponse;
use Pin\Errors\IError;

/**
 * Pin TestResponse
 *
 * 对 Laravel TestResponse 的轻量包装，提供统一的业务响应断言能力
 *
 * @mixin BaseResponse
 */
class TestResponse
{
    use Assertions\AssertMessage,
        Assertions\AssertMutation,
        Assertions\AssertPagination,
        Assertions\AssertValidation;

    /**
     * Laravel 原生 TestResponse 实例。
     */
    public function __construct(public protected(set) BaseResponse $response)
    {
    }

    /**
     * 转发未定义方法到底层 Laravel TestResponse。
     */
    public function __call(string $method, array $arguments): mixed
    {
        $result = $this->response->{$method}(...$arguments);

        return $result instanceof BaseResponse ? $this : $result;
    }

    /**
     * 业务码断言
     *
     * @param  int  $code  业务状态码
     * @param  int|null  $status  HTTP 状态码
     */
    public function assertCode(int|IError $code, ?int $status = null): static
    {
        $code = is_int($code) ? $code : $code->code();
        $this->response->assertJsonPath('code', $code);

        if ($status !== null) {
            $this->response->assertStatus($status);
        }

        return $this;
    }

    /**
     * 通用成功断言
     */
    public function assertSuccessful(): static
    {
        $this->response->assertOk()->assertJsonPath('code', 0);

        return $this;
    }
}
