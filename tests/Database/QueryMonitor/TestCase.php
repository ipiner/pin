<?php

namespace Pin\Tests\Database\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;
use PDO;

trait TestCase
{
    protected function getQueryExecuted(
        ?string $sql = null,
        ?array $bindings = null,
        int $time = 1000
    ): QueryExecuted {
        return new QueryExecuted(
            $sql ?: 'select username from users where uid = ?',
            $bindings === null ? [1] : $bindings,
            $time,
            $this->getConnection()
        );
    }
}

class MockPDO extends PDO
{
    public function __construct()
    {
        parent::__construct('sqlite::memory:');
    }
}
