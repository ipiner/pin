<?php

declare(strict_types=1);

namespace Pin\Models\Cache;

use Pin\Models\Model;

/**
 * 缓存 Key 生成器
 *
 * 用于统一生成模型相关缓存 Key
 *
 * Key 规范：
 * 1. 单条记录：
 *    {table}:{field}
 *    示例：users:1001
 *
 * 2. 全量数据：
 *    {table}-all
 *    示例：users-all
 */
class KeyGenerator
{
    /**
     * 生成全量数据缓存 Key
     */
    public static function forAll(string|Model $table): string
    {
        $table = is_string($table) ? $table : $table->getTable();

        return $table.'-all';
    }

    /**
     * 生成单条记录缓存 Key
     */
    public static function forItem(string|Model $table, int|string $field): string
    {
        $table = is_string($table) ? $table : $table->getTable();

        return $table.':'.$field;
    }
}
