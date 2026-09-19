<?php

declare(strict_types=1);

namespace Pin\Models\Cache;

use Pin\Models\Model;

/**
 * 模型缓存键生成器
 */
class KeyGenerator
{
    /**
     * 生成全量缓存键
     */
    public static function forAll(string|Model $table): string
    {
        $table = is_string($table) ? $table : $table->getTable();

        return $table.'-all';
    }

    /**
     * 生成单条缓存键
     */
    public static function forItem(string|Model $table, int|string $field): string
    {
        $table = is_string($table) ? $table : $table->getTable();

        return $table.':'.$field;
    }
}
