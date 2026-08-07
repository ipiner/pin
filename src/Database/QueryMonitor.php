<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Database\Events\QueryExecuted;
use Pin\Database\QueryMonitor\QueryLogger;
use Pin\Database\QueryMonitor\QueryProfile;
use Pin\Database\QueryMonitor\QueryResponse;
use Pin\Database\QueryMonitor\QuerySql;

/**
 * SQL 监控入口（QueryExecuted 事件处理器）
 */
class QueryMonitor
{
    public function __construct(
        public QueryProfile $profile,
        public QueryLogger $logger,
        public QueryResponse $response,
    ) {
        //
    }

    /**
     * 入口
     */
    public function handle(QueryExecuted $event): void
    {
        $this->profile->record($event);

        $sql = QuerySql::raw($event);

        $this->response->push($event, $sql);
        $this->logger->push($event, $sql);
    }
}
