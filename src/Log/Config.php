<?php

declare(strict_types=1);

namespace Pin\Log;

/**
 * 日志配置助手
 */
class Config
{
    /**
     * 创建按天滚动日志配置
     */
    public static function daily(string $name, array $options = []): array
    {
        $options = [
            'driver' => 'daily',
            'days' => 14,
            ...$options,
        ];

        return static::single($name, $options);
    }

    /**
     * 创建单文件日志配置
     */
    public static function single(string $name, array $options = []): array
    {
        return [
            'driver' => 'single',

            // 日志文件路径
            'path' => static::resolveLogPath($name),

            // 日志等级
            'level' => env('LOG_'.strtoupper($name).'_LEVEL') ?: 'debug',

            // 文件权限
            'permission' => 0777,

            // JSON 日志格式化
            'formatter' => JsonFormatter::class,

            // 日志扩展处理器
            'tap' => [ExtraTapper::class],

            // 替换占位符
            'replace_placeholders' => true,

            // Channel 名称
            'name' => $name,

            ...$options,
        ];
    }

    /**
     * 解析日志文件路径
     */
    protected static function resolveLogPath(string $name, ?string $env = null): string
    {
        $env ??= env('APP_ENV');

        // testing 环境使用独立日志目录
        $path = $env === 'testing' ? 'testing-logs' : 'logs';

        return storage_path("{$path}/{$name}.log");
    }
}
