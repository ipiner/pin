<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Services\Results\Result;

/**
 * 基础控制器
 *
 * 提供统一的成功和错误响应方法
 */
class Controller extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * /**
     *  创建错误响应
     */
    public function error(
        int|IError $code,
        string $message = '',
        mixed $data = null,
        ?array $meta = null
    ): ApiResponse {
        $code = $code instanceof IError ? $code->code() : $code;

        return ApiResponse::make(
            $code ?: Errors::Failed,
            $message,
            $data,
            $meta
        );
    }

    /**
     * 创建成功响应
     *
     * 参数顺序采用：
     *
     * `$data, $message, $meta`
     *
     * 原因：
     * - 成功响应最常见场景是仅返回数据
     * - 因此将 `$data` 放在第一位，减少调用时的冗余参数
     */
    public function success(
        mixed $data = null,
        string $message = '',
        ?array $meta = null
    ): ApiResponse {
        if ($message === '' && $data instanceof Result) {
            $message = $data->message();
        }

        return ApiResponse::make(
            message: $message,
            data: $data,
            meta: $meta
        );
    }
}
