<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;

/**
 * SQL 响应收集器（仅用于调试模式）。
 */
class QueryResponse
{
    /**
     * 响应中返回的 SQL 列表。
     *
     * @var array{sql: string, duration: int}[]
     */
    protected array $sqls = [];

    /**
     * 记录 SQL。
     */
    public function push(QueryExecuted $event, string $sql): void
    {
        if ($this->shouldResponse()) {
            $this->sqls[] = ['sql' => $sql, 'time' => (int) $event->time];
        }
    }

    /**
     * 返回所有 SQL。
     *
     * @return array<int, string>
     */
    public function all(): array
    {
        return $this->sqls;
    }

    /**
     * 是否在响应中暴露 SQL。
     *
     * debug 或显式开启时生效。
     */
    protected function shouldResponse(): bool
    {
        return config('app.debug')
            || config('pin.logging.response.include_sql');
    }
}
