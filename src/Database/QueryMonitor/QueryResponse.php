<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Closure;
use Illuminate\Database\Events\QueryExecuted;

/**
 * SQL 响应收集器。
 */
class QueryResponse
{
    /**
     * 响应中返回的 SQL 列表。
     *
     * @var list<array{sql: string, time: int}>
     */
    protected array $queries = [];

    /**
     * 记录 SQL。
     */
    public function push(QueryExecuted $event, string|Closure $sql): void
    {
        if (! $this->shouldRespond()) {
            return;
        }

        $this->queries[] = ['sql' => value($sql), 'time' => (int) $event->time];
    }

    /**
     * 返回所有 SQL。
     *
     * @return list<array{sql: string, time: int}>
     */
    public function all(): array
    {
        return $this->queries;
    }

    /**
     * 是否在响应中附带 SQL。
     */
    protected function shouldRespond(): bool
    {
        return config('app.debug')
            || config('pin.logging.response.include_sql');
    }
}
