<?php

declare(strict_types=1);

namespace Pin\Route;

/**
 * 路由扫描路径
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
    }
}
