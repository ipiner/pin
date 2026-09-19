<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Testing\TestResponse as BaseResponse;
use Pin\Errors\IError;

/**
 * 业务响应断言
 *
 * @mixin BaseResponse
 */
class TestResponse
{
    use Assertions\AssertMessage;
    use Assertions\AssertMutation;
    use Assertions\AssertPagination;
    use Assertions\AssertValidation;

    public function __construct(public protected(set) BaseResponse $response)
    {
    }

    /**
     * 转发响应方法
     */
    public function __call(string $method, array $arguments): mixed
    {
        $result = $this->response->{$method}(...$arguments);

        return $result instanceof BaseResponse ? $this : $result;
    }

    /**
     * 业务码断言
     *
     * @param  int|IError  $code  业务状态码
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
