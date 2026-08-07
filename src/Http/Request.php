<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Http\Request as BaseRequest;

/**
 * Laravel Request 增强工具类
 */
class Request
{
    /**
     * 需要注册到 Laravel Request 的宏方法列表
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
     * 注册宏方法到 Illuminate\Http\Request
     */
    public static function registerMacros(): void
    {
        foreach (static::MACROS as $method) {
            BaseRequest::macro($method, function (...$parameters) use ($method) {
                /** @var BaseRequest $this */
                return Request::{$method}($this, ...$parameters);
            });
        }
    }

    /**
     * 获取请求来源 Referer
     */
    public static function getReferer(BaseRequest $request): string
    {
        $from = $request->header('x-referer') ?: $request->header('referer');

        return $from ? urldecode($from) : '';
    }

    /**
     * 请求是否来自 API 文档。
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
     * 请求是否为读取请求
     *
     * HEAD、GET、OPTIONS 请求视为读取操作
     */
    public static function isReading(BaseRequest $request): bool
    {
        return in_array(strtoupper($request->method()), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * 请求是否匹配指定规则
     *
     * @param  string|array  $values  URI 或路由名称规则。
     */
    public static function isRequest(BaseRequest $request, string|array $values): bool
    {
        $values = (array) $values;
        foreach ($values as $s) {
            if ($request->is(ltrim($s, '/')) || $request->routeIs($s)) {
                return true;
            }
        }

        return false;
    }
}
