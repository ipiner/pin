<?php

declare(strict_types=1);

namespace Pin\Support;

use Closure;
use Illuminate\Support\Arr;

/**
 * 调用栈解析器
 *
 * 从 PHP debug_backtrace 中解析“最有意义的业务调用点”，
 */
class Caller
{
    /**
     * 自定义“业务文件判断逻辑”
     */
    protected static ?Closure $applicationFileResolver = null;

    /**
     * 解析调用栈中的“业务调用点”
     *
     * @param  array  $backtrace  可选的预生成 backtrace）
     * @return array{file: string, line: int}
     */
    public static function resolve(array $backtrace = []): array
    {
        $backtrace = $backtrace ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // 查找第一个“业务代码调用点”
        $item = Arr::first($backtrace, static function ($item) {
            return isset($item['file']) && static::isApplicationFile($item['file']);
        });

        if ($item) {
            return $item;
        }

        // fallback：返回第一个可用调用信息
        return Arr::first(
            $backtrace,
            fn ($item) => isset($item['file'], $item['line']),
            [
                'file' => $backtrace[0]['file'] ?? 'unknown',
                'line' => $backtrace[0]['line'] ?? 0,
            ]
        );
    }

    /**
     * 注入“业务文件识别规则”
     *
     * 替换默认的 vendor 过滤逻辑，定义：什么文件属于“业务代码（application code）”
     *
     * @param  Closure  $resolver  `function (string $file): bool`
     */
    public static function setApplicationFileResolver(?Closure $resolver): void
    {
        static::$applicationFileResolver = $resolver;
    }

    /**
     * 判断某个文件是否属于“业务代码”
     *
     * @param  string  $file  文件路径
     */
    protected static function isApplicationFile(string $file): bool
    {
        $resolver = static::$applicationFileResolver
            ?? fn () => ! str_contains($file, 'vendor');

        return $resolver($file);
    }
}
