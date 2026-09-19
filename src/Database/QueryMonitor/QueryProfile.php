<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;

/**
 * SQL 执行统计。
 */
class QueryProfile
{
    /**
     * SQL 执行次数。
     */
    public int $count = 0;

    /**
     * SQL 总耗时（毫秒）。
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
     * 是否慢查询。
     */
    public function isSlow(QueryExecuted $event): bool
    {
        return $event->time >= $this->slowThreshold($event);
    }

    /**
     * 慢查询阈值（毫秒），配置值不大于 10 时按秒换算。
     */
    protected function slowThreshold(QueryExecuted $event): int
    {
        $threshold = (float) config(
            'database.connections.'.$event->connectionName.'.slow_threshold',
            2000
        );

        return $threshold <= 10
            ? (int) ($threshold * 1000)
            : (int) $threshold;
    }
}
