<?php

declare(strict_types=1);

namespace Pin\Route;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * RouteScanner
 *
 * PSR-4 方式扫描 Route Enum
 */
class RouteScanner
{
    /**
     * 扫描 Route Enum
     *
     * @param  string[]  $paths
     * @return class-string<Routable>[]
     */
    public function scan(array $paths): array
    {
        return collect($paths)
            ->flatMap(
                fn (string $path) => $this->scanPath($path)
            )
            ->all();
    }

    /**
     * PSR-4 class 解析
     */
    protected function resolveClassFromFile(SplFileInfo $file): ?string
    {
        // <base_path>/app/Routes/UserRoute.php -> app\Routes\UserRoute
        $class = str_replace(
            [base_path(DIRECTORY_SEPARATOR), '/'],
            ['', '\\'],
            substr($file->getRealPath(), 0, -4)
        );

        // App\Routes\UserRoute
        return ucfirst($class);
    }

    /**
     * 扫描单个路径
     *
     * @return class-string<Routable>[]
     */
    protected function scanPath(string $path): array
    {
        $finder = new Finder();
        $finder->files()->in($path)->name('*Route.php');

        $items = [];
        foreach ($finder as $file) {
            $class = $this->resolveClassFromFile($file);
            if (is_subclass_of($class, Routable::class)) {
                $items[] = $class;
            }
        }

        return $items;
    }
}
