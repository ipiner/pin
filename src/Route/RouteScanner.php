<?php

declare(strict_types=1);

namespace Pin\Route;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Route Enum 扫描器
 *
 * 按 PSR-4 规则扫描并解析 Route Enum。
 */
class RouteScanner
{
    /**
     * 扫描 Route Enum
     *
     * @param  array<string|RouteScanPath>  $paths
     * @return class-string<Routable>[]
     */
    public function scan(array $paths): array
    {
        return collect($paths)
            ->flatMap(function (string|RouteScanPath $path) {
                $path = is_string($path) ? new RouteScanPath($path) : $path;

                return $this->scanPath($path);
            })
            ->values()
            ->all();
    }

    /**
     * 根据文件路径解析 Class
     */
    protected function resolveClassFromFile(
        SplFileInfo $file,
        RouteScanPath $path,
    ): ?string {
        $basePath = realpath($path->path);
        $filePath = $file->getRealPath();

        $relativePath = ltrim(
            str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr($filePath, strlen($basePath))
            ),
            '/',
        );

        // <base_path>/app/Routes/UserRoute.php -> UserRoute
        $class = str_replace(
            '/',
            '\\',
            substr($relativePath, 0, -strlen('.php')),
        );

        if ($path->namespace) {
            return trim($path->namespace, '\\').'\\'.$class;
        }

        return 'App\\Routes\\'.$class;
    }

    /**
     * 扫描单个路径
     *
     * @return class-string<Routable>[]
     */
    protected function scanPath(RouteScanPath $path): array
    {
        $finder = new Finder()
            ->files()
            ->in($path->path)
            ->name($path->pattern);

        $items = [];

        foreach ($finder as $file) {
            $class = $this->resolveClassFromFile(
                $file,
                $path,
            );

            if (enum_exists($class) && is_subclass_of($class, Routable::class)) {
                $items[] = $class;
            }
        }

        return $items;
    }
}
