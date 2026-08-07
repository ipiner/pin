<?php

declare(strict_types=1);

namespace Pin\Log;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Throwable;

/**
 * Monolog 日志额外处理器
 *
 * 为每条日志注入额外上下文信息（extra）：
 * - 当前用户 ID
 * - 请求 ID、IP、方法、URL
 * - 当前路由名称或 URI
 *
 * 注册方式：
 * 在 Monolog channel 的 processors 配置中使用。
 */
class ExtraProcessor implements ProcessorInterface
{
    /**
     * 获取额外上下文
     *
     * @return array{
     *     uid:int|null,
     *     request_id:string,
     *     request_method:string,
     *     request_url:string,
     *     route:string,
     *     ip:string
     * }
     */
    public static function getExtra(): array
    {
        return app()->runningInHttp() ? static::extraForHttp() : static::extraForConsole();
    }

    /**
     * 获取路由信息
     */
    public static function getRoute(?Route $route = null): string
    {
        $route ??= app()->request->route();
        if (! $route) {
            return '';
        }

        $name = $route->getName();

        // 优先返回路由名称，自动忽略自动生成的路由名
        return $name && ! str_contains($name, 'generated::') ? $name : $route->uri();
    }

    /**
     * Monolog Processor 接口
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $record['extra'] = array_merge($record['extra'], static::getExtra());

        return $record;
    }

    /**
     * 基础 extra
     */
    protected static function basicExtra(): array
    {
        return [
            'uid' => static::getUid(),
            'ip' => Request::ip(),
            'request_id' => app()->getRequestId(),
            'route' => static::getRoute(),
        ];
    }

    /**
     * CLI 上下文
     */
    protected static function extraForConsole(): array
    {
        return [
            ...static::basicExtra(),
            'request_method' => 'console',
            'request_url' => implode(' ', Request::server('argv')),
        ];
    }

    /**
     * HTTP 上下文
     */
    protected static function extraForHttp(): array
    {
        return [
            ...static::basicExtra(),
            'request_method' => Request::method(),
            'request_url' => urldecode(Request::fullUrl()),
        ];
    }

    /**
     * 获取当前用户 ID
     */
    protected static function getUid(): ?int
    {
        try {
            return auth()->id();
        } catch (Throwable) {
            return null;
        }
    }
}
