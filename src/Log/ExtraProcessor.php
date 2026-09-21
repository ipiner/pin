<?php

declare(strict_types=1);

namespace Pin\Log;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Override;
use Throwable;

/**
 * 日志上下文处理器
 */
class ExtraProcessor implements ProcessorInterface
{
    /**
     * 获取日志上下文
     *
     * @return array{
     *     uid:int|null,
     *     request_id:string,
     *     request_method:string,
     *     request_url:string,
     *     route:string,
     *     ip:string|null
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

        return $name && ! str_starts_with($name, 'generated::') ? $name : $route->uri();
    }

    /**
     * 补充日志上下文
     */
    #[Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra = array_merge($record->extra, static::getExtra());

        return $record;
    }

    /**
     * 基础上下文
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
     * 命令行上下文
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
