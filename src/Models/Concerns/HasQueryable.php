<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Trait HasQueryable
 *
 * 提供模型级 Query DSL 字段转换能力
 *
 * - 在查询构建阶段统一处理字段名格式
 * - 将前端/DSL 输入字段转换为数据库可识别字段
 * - 作为 QueryScope / Query Engine 的模型扩展
 */
trait HasQueryable
{
    /**
     * 转换查询字段名
     *
     * 将 Query DSL / API 输入字段转换为数据库可用字段
     *
     * @param  string  $column  查询字段名（来自 API / DSL）
     */
    public function transformQueryableColumn(string $column): string
    {
        return Str::snake($column);
    }
}
