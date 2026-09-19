<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Database\Seeder;
use Symfony\Component\Finder\Finder;

/**
 * 数据填充调度器。
 */
class DatabaseSeeder extends Seeder
{
    /**
     * 执行数据填充。
     */
    public function run(): static
    {
        return $this->call($this->seeders());
    }

    /**
     * 扫描数据填充类。
     *
     * @return list<class-string<Seeder>>
     */
    protected function seeders(?string $path = null): array
    {
        $path ??= database_path('seeders');

        if (! is_dir($path)) {
            return [];
        }

        $files = Finder::create()->files()->in($path)->name('*Seeder.php');

        $seeders = [];
        foreach ($files as $file) {
            $class = 'Database\\Seeders\\'.str_replace(
                '/',
                '\\',
                substr($file->getRelativePathname(), 0, -4)
            );

            if ($class !== static::class) {
                $seeders[] = $class;
            }
        }

        return $seeders;
    }
}
