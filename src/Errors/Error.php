<?php

declare(strict_types=1);

namespace Pin\Errors;

use Illuminate\Http\Response;
use Pin\Support\Facades\RuntimeCache;

/**
 * 统一错误结构
 *
 * 用于将业务错误（IError）标准化为框架内部统一格式：
 * - code: 业务错误码
 * - messageKey: 错误信息翻译key
 * - statusCode: HTTP 状态码
 */
class Error
{
    /**
     * @param  int  $code  业务错误码
     * @param  string  $messageKey  错误信息翻译key
     * @param  int  $statusCode  HTTP 状态码（默认 500）
     */
    public function __construct(
        public int $code,
        public string $messageKey,
        public int $statusCode = 500
    ) {
        /**
         * statusCode 归一化规则
         *
         * 当传入 0 时：
         * - 若 code 是合法 HTTP 状态码，则直接使用
         * - 否则默认视为业务错误（200）
         */
        if ($statusCode === 0) {
            $this->statusCode = $code > 100 && $code < 600 && isset(Response::$statusTexts[$code])
                ? $code
                : 200;
        }
    }

    /**
     * 将 IError 枚举转换为标准错误对象
     *
     * 结果会被缓存，避免重复解析相同错误定义
     */
    public static function parse(IError $err): static
    {
        // 允许错误码覆盖，优先从注册中心中查找
        $code = (int) explode('|', $err->value)[0];
        $case = Registry::all()[$code] ?? $err;

        $key = get_class($case).'.'.$case->name;

        return RuntimeCache::rememberForever($key, fn () => static::parseInternal($case->value));
    }

    /**
     * 解析枚举值为错误结构
     *
     * 支持格式：
     * - code|status|message
     * - code|message
     */
    protected static function parseInternal(string $value): static
    {
        $parts = explode('|', $value, 3);

        $code = $parts[0];
        $statusCode = isset($parts[2]) ? $parts[1] : 0;
        $message = $parts[2] ?? $parts[1];

        return new static(
            (int) $code,
            $message,
            (int) $statusCode
        );
    }
}
