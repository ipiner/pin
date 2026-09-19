<?php

declare(strict_types=1);

namespace Pin\Route;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * 路由枚举扫描器
 */
class RouteScanner
{
    /**
     * 扫描路由枚举
     *
     * @param  array<string|int, string|RouteScanPath>  $paths
     * @return list<class-string<Routable>>
     */
    public function scan(array $paths): array
    {
        $routes = [];

        foreach ($paths as $key => $path) {
            if (! $path instanceof RouteScanPath) {
                $path = new RouteScanPath(
                    path: is_string($key) ? $key : $path,
                    namespace: is_string($key) ? $path : null,
                );
            }

            array_push($routes, ...$this->scanPath($path));
        }

        return $routes;
    }

    /**
     * 根据相对路径解析类名
     */
    protected function resolveClassFromFile(
        SplFileInfo $file,
        RouteScanPath $path,
    ): ?string {
        $class = str_replace(
            DIRECTORY_SEPARATOR,
            '\\',
            substr($file->getRelativePathname(), 0, -strlen('.php')),
        );

        return trim($path->namespace ?: 'App\\Routes', '\\').'\\'.$class;
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

        $routes = [];

        foreach ($finder as $file) {
            $class = $this->resolveClassFromFile($file, $path);

            if ($class && enum_exists($class) && is_subclass_of($class, Routable::class)) {
                $routes[] = $class;
            }
        }

        return $routes;
    }
}
