<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Services\Results\Result;

/**
 * 基础控制器
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * 创建错误响应
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
