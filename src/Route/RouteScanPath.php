<?php

declare(strict_types=1);

namespace Pin\Route;

/**
 * Route 扫描路径配置
 *
 * 用于定义 Route Enum 的扫描目录、命名空间及文件匹配规则。
 */
final readonly class RouteScanPath
{
    /**
     * @param  string  $path  扫描目录
     * @param  string|null  $namespace  命名空间
     * @param  string  $pattern  文件匹配规则
     */
    public function __construct(
        public string $path,
        public ?string $namespace = null,
        public string $pattern = '*Route.php',
    ) {
        //
    }
}
