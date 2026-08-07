<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Str;
use Pin\Models\Model;
use Pin\Support\Facades\RuntimeCache;

/**
 * 表 Metadata 解析器。
 *
 * 从 schema 文件加载表级元数据。
 */
class Metadata
{
    /**
     * 表显示名称
     */
    public protected(set) string $label;

    /**
     * 字段元数据
     */
    public protected(set) array $attributes;

    /**
     * 创建 Metadata 实例
     */
    public function __construct(protected string $connection, protected string $table)
    {
        $schemas = $this->load();
        $this->label = $schemas['label'] ?? Str::title($this->table);
        $this->attributes = $schemas['attributes'] ?? [];
    }

    /**
     * 加载 schema 文件。
     *
     * @param  string|class-string<Model>
     */
    public static function make(string $connection, ?string $table = null): static
    {
        return RuntimeCache::rememberForever(
            $connection.$table.'.meta',
            function () use ($connection, $table) {
                if (! $table) {
                    $model = new $connection();
                    $connection = $model->getConnectionName() ?: config('database.default');
                    $table = $model->getTable();
                }

                return new Metadata($connection, $table);
            }
        );
    }

    /**
     * 加载 schema 文件
     *
     * 文件路径：
     *
     * ```txt
     * database/schemas/{connection}/{table}.php
     * ```
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
