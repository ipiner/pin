<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Database\Seeder;
use Symfony\Component\Finder\Finder;

/**
 * 系统数据库 Seeder 调度器
 *
 * 用于自动扫描并执行项目中所有 Seeder 类，
 * 替代 Laravel 默认手动 `$this->call([...])` 的方式。
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): static
    {
        return $this->call($this->seeders());
    }

    /**
     * 自动扫描并解析所有 Seeder 类
     *
     * Database/Seeders/UserSeeder.php → Database\Seeders\UserSeeder
     */
    protected function seeders(?string $path = null): array
    {
        $path ??= database_path('seeders');
        $finder = new Finder();
        $finder->files()->in($path)->name('*Seeder.php');

        $seeders = [];
        foreach ($finder as $file) {
            $s = str_replace(
                '/',
                '\\',
                str_replace([$path, '.php'], '', $file->getPathname())
            );
            $class = sprintf(
                'Database\Seeders\\%s',
                trim($s, '\\'),
            );

            if ($class !== static::class) {
                $seeders[] = $class;
            }
        }

        return $seeders;
    }
}
