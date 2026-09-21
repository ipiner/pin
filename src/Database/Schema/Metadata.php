<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Str;
use Pin\Models\Model;
use Pin\Support\Facades\RuntimeCache;

/**
 * 数据表元数据
 */
class Metadata
{
    /**
     * 表名称
     */
    public protected(set) string $label;

    /**
     * 字段名称映射
     *
     * @var array<string, string>
     */
    public protected(set) array $attributes;

    public function __construct(protected string $connection, protected string $table)
    {
        $metadata = $this->load();
        $this->label = $metadata['label'] ?? Str::headline(Str::singular($this->table));
        $this->attributes = $metadata['attributes'] ?? [];
    }

    /**
     * 获取表元数据
     *
     * @param  string|class-string<Model>  $connection
     */
    public static function make(string $connection, ?string $table = null): static
    {
        return RuntimeCache::rememberForever(
            static::class.":{$connection}:{$table}",
            static function () use ($connection, $table) {
                if (! $table) {
                    $model = new $connection();
                    $connection = $model->getConnectionName() ?: config('database.default');
                    $table = $model->getTable();
                }

                return new static($connection, $table);
            }
        );
    }

    /**
     * 加载元数据文件
     */
    protected function load(): array
    {
        $key = "schemas/{$this->connection}/{$this->table}.php";

        return RuntimeCache::rememberForever($key, function () use ($key) {
            $file = database_path($key);

            return is_file($file) ? require $file : [];
        });
    }
}
