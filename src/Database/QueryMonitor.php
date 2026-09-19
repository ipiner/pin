<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Database\Events\QueryExecuted;
use Pin\Database\QueryMonitor\QueryLogger;
use Pin\Database\QueryMonitor\QueryProfile;
use Pin\Database\QueryMonitor\QueryResponse;
use Pin\Database\QueryMonitor\QuerySql;

/**
 * SQL 查询监控。
 */
class QueryMonitor
{
    public function __construct(
        public QueryProfile $profile,
        public QueryLogger $logger,
        public QueryResponse $response,
    ) {
    }

    /**
     * 记录查询。
     */
    public function handle(QueryExecuted $event): void
    {
        $this->profile->record($event);

        $sql = null;
        $resolveSql = static function () use ($event, &$sql): string {
            return $sql ??= QuerySql::raw($event);
        };

        $this->response->push($event, $resolveSql);
        $this->logger->push($event, $resolveSql);
    }
}
