<?php

declare(strict_types=1);

namespace Pin\Errors;

use Illuminate\Http\Response;
use Pin\Support\Facades\RuntimeCache;

/**
 * 错误定义
 */
class Error
{
    /**
     * @param  int  $code  业务错误码
     * @param  string  $messageKey  消息翻译键
     * @param  int  $statusCode  HTTP 状态码
     */
    public function __construct(
        public int $code,
        public string $messageKey,
        public int $statusCode = 500
    ) {
        if (! $statusCode) {
            $this->statusCode = $code > 100 && $code < 600 && isset(Response::$statusTexts[$code])
                ? $code
                : 200;
        }
    }

    /**
     * 解析并缓存错误定义
     */
    public static function parse(IError $err): static
    {
        $case = Registry::resolve($err);
        $key = static::class.'.'.$case::class.'.'.$case->name;

        return RuntimeCache::rememberForever(
            $key,
            static fn () => static::parseInternal($case->value)
        );
    }

    /**
     * 解析 code|message 或 code|status|message
     */
    protected static function parseInternal(string $value): static
    {
        $parts = explode('|', $value, 3);

        return new static(
            (int) $parts[0],
            $parts[2] ?? $parts[1],
            isset($parts[2]) ? (int) $parts[1] : 0
        );
    }
}
