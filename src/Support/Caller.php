<?php

declare(strict_types=1);

namespace Pin\Support;

use Closure;

/**
 * 业务调用栈解析
 */
class Caller
{
    /**
     * @var (Closure(string): bool)|null
     */
    protected static ?Closure $applicationFileResolver = null;

    /**
     * 获取首个业务调用点
     *
     * @return array{file: string, line: int, ...}
     */
    public static function resolve(array $backtrace = []): array
    {
        $backtrace = $backtrace ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $fallback = null;

        foreach ($backtrace as $frame) {
            if (! isset($frame['file'], $frame['line'])) {
                continue;
            }

            $fallback ??= $frame;

            if (static::isApplicationFile($frame['file'])) {
                return $frame;
            }
        }

        return $fallback ?? [
            'file' => $backtrace[0]['file'] ?? 'unknown',
            'line' => $backtrace[0]['line'] ?? 0,
        ];
    }

    /**
     * 设置业务文件识别规则
     *
     * @param  (Closure(string): bool)|null  $resolver
     */
    public static function setApplicationFileResolver(?Closure $resolver): void
    {
        static::$applicationFileResolver = $resolver;
    }

    /**
     * 判断是否为业务文件
     */
    protected static function isApplicationFile(string $file): bool
    {
        if (static::$applicationFileResolver) {
            return (static::$applicationFileResolver)($file);
        }

        return ! str_contains('/'.str_replace('\\', '/', $file), '/vendor/');
    }
}
