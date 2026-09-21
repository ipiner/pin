<?php

declare(strict_types=1);

namespace Pin\Log;

use Monolog\Formatter\NormalizerFormatter;

/**
 * 日志配置助手
 */
class Config
{
    /**
     * 默认日志格式化器
     *
     * @var class-string<NormalizerFormatter>|null
     */
    public static ?string $defaultFormatter = JsonFormatter::class;

    /**
     * 创建按天滚动日志配置
     */
    public static function daily(string $name, array $options = []): array
    {
        return static::single($name, [
            'driver' => 'daily',
            'days' => 14,
            ...$options,
        ]);
    }

    /**
     * 创建单文件日志配置
     */
    public static function single(string $name, array $options = []): array
    {
        return [
            'driver' => 'single',
            'path' => static::resolveLogPath($name),
            'level' => env('LOG_'.strtoupper($name).'_LEVEL') ?: 'debug',
            'permission' => 0777,
            'formatter' => static::$defaultFormatter,
            'tap' => [ExtraTapper::class],
            'replace_placeholders' => true,
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

        $path = $env === 'testing' ? 'testing-logs' : 'logs';

        return storage_path("{$path}/{$name}.log");
    }
}
