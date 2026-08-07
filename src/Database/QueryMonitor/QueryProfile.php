<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;

/**
 * SQL 执行性能统计（请求级）。
 *
 * 仅用于当前请求生命周期内的 SQL 统计。
 */
class QueryProfile
{
    /**
     * SQL 执行次数
     */
    public int $count = 0;

    /**
     * SQL 总耗时（ms）
     */
    public int $time = 0;

    /**
     * 记录 SQL 执行。
     */
    public function record(QueryExecuted $event): void
    {
        $this->count++;
        $this->time += (int) $event->time;
    }

    /**
     * 是否慢查询
     */
    public function isSlow(QueryExecuted $event): bool
    {
        return $event->time >= $this->slowThreshold($event);
    }

    /**
     * 慢查询阈值（ms）。
     *
     * 配置规则：
     *  - 'database.connections.{connection}.slow_threshold'
     *
     *  兼容两种输入方式：
     *  - ≤ 10：视为“秒”，自动转 ms（如 2 => 2000ms）
     *  - > 10：视为已是 ms
     */
    protected function slowThreshold(QueryExecuted $event): int
    {
        $value = (float) config(
            'database.connections.'.$event->connectionName.'.slow_threshold',
            2000
        );

        return $value <= 10
            ? (int) ($value * 1000)
            : (int) $value;
    }
}
