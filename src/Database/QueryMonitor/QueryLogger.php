<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

/**
 * SQL 日志收集器
 */
class QueryLogger
{
    /**
     * SQL 队列
     *
     * @var list<array{sql: string, context: array}>
     */
    protected array $queries = [];

    public function __construct(protected QueryProfile $profile)
    {
    }

    /**
     * 批量写入 SQL 日志
     */
    public function flush(): void
    {
        if (! $this->queries) {
            return;
        }

        $logger = Log::channel('sql');
        $queries = $this->queries;
        $this->queries = [];

        foreach ($queries as $query) {
            $logger->log(
                $this->resolveLogLevel($query['context']),
                $query['sql'],
                $query['context']
            );
        }
    }

    /**
     * 收集 SQL 日志
     */
    public function push(QueryExecuted $event, string|Closure $sql): void
    {
        $slow = $this->profile->isSlow($event);

        if (! $this->shouldLog($event, $slow)) {
            return;
        }

        $this->queries[] = [
            'sql' => value($sql),
            'context' => [
                'category' => 'sql',
                'connection' => $event->connectionName,
                'time' => (int) $event->time,
                'slow' => $slow,
            ],
        ];
    }

    /**
     * 是否忽略 SQL
     */
    protected function isIgnored(QueryExecuted $event): bool
    {
        return array_any(
            config('logging.channels.sql.ignores', []),
            fn (string $rule) => $this->matchIgnore($event->sql, $rule)
        );
    }

    /**
     * 匹配正则或包含规则
     */
    protected function matchIgnore(string $sql, string $rule): bool
    {
        if (str_starts_with($rule, '/')) {
            return preg_match($rule, $sql) === 1;
        }

        return str_contains($sql, $rule);
    }

    /**
     * 获取日志等级
     *
     * @param  array{slow: bool}  $context
     */
    protected function resolveLogLevel(array $context): string
    {
        return $context['slow'] ? LogLevel::NOTICE : LogLevel::DEBUG;
    }

    /**
     * 是否记录 SQL
     */
    protected function shouldLog(QueryExecuted $event, bool $slow): bool
    {
        if (! $slow && ! config('app.debug') && ! config('pin.logging.sql_logging')) {
            return false;
        }

        return ! $this->isIgnored($event);
    }
}
