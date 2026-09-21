<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Http\Request as BaseRequest;

/**
 * 请求宏
 */
class Request
{
    /**
     * 宏方法列表
     *
     * @var array<int, string>
     */
    protected const array MACROS = [
        'getReferer',
        'isFromApiDocument',
        'isReading',
        'isRequest',
    ];

    /**
     * 注册请求宏
     */
    public static function registerMacros(): void
    {
        $class = static::class;

        foreach (static::MACROS as $method) {
            BaseRequest::macro($method, function (...$parameters) use ($class, $method) {
                /** @var BaseRequest $this */
                return $class::{$method}($this, ...$parameters);
            });
        }
    }

    /**
     * 获取请求来源 Referer
     */
    public static function getReferer(BaseRequest $request): string
    {
        $referer = $request->header('x-referer') ?: $request->header('referer');

        return $referer ? urldecode($referer) : '';
    }

    /**
     * 请求是否来自 API 文档
     */
    public static function isFromApiDocument(BaseRequest $request): bool
    {
        $config = config('app.x_api_document');

        if (! $config['enabled']) {
            return false;
        }

        $value = $request->header('x-api-document');

        if ($value && in_array($value, $config['allows'], true)) {
            return true;
        }

        $value = parse_url($request->header('referer', ''), PHP_URL_PATH);

        return $value && in_array(trim($value, '/'), $config['allows'], true);
    }

    /**
     * 是否为读取请求（HEAD、GET、OPTIONS）
     */
    public static function isReading(BaseRequest $request): bool
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS'], true);
    }

    /**
     * 请求是否匹配指定规则
     *
     * @param  string|array  $values  URI 或路由名称规则
     */
    public static function isRequest(BaseRequest $request, string|array $values): bool
    {
        foreach ((array) $values as $pattern) {
            if ($request->is(ltrim($pattern, '/')) || $request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
