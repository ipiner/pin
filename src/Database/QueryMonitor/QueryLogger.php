<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

/**
 * SQL 日志收集器
 */
class QueryLogger
{
    /**
     * SQL 队列。
     *
     * [
     *   'sql' => string,
     *   'context' => array
     * ]
     *
     * @var array<int, array{sql:string, context:array}>
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
        foreach ($this->queries as $query) {
            Log::channel('sql')->log(
                $this->resolveLogLevel($query['context']),
                $query['sql'],
                $query['context']
            );
        }

        $this->queries = [];
    }

    /**
     * 记录 SQL 到内存队列（延迟写入）。
     *
     * @param  string  $sql  已格式化 SQL 字符串
     */
    public function push(QueryExecuted $event, string $sql): void
    {
        if (! $this->shouldLog($event)) {
            return;
        }

        $this->queries[] = [
            'sql' => $sql,
            'context' => [
                'category' => 'sql',
                'connection' => $event->connectionName,
                'time' => (int) $event->time,
                'slow' => $this->profile->isSlow($event),
            ],
        ];
    }

    /**
     * 是否忽略 SQL
     */
    protected function isIgnored(QueryExecuted $event): bool
    {
        $sql = $event->sql;

        return array_any(
            config('logging.channels.sql.ignores', []),
            fn (string $rule) => $this->matchIgnore($sql, $rule)
        );
    }

    /**
     * ignore 规则匹配（regex / contains）
     *
     * @param  string  $sql  SQL 内容
     * @param  string  $term  规则字符串
     */
    protected function matchIgnore(string $sql, string $term): bool
    {
        // 正则规则（以 / 开头）
        if (str_starts_with($term, '/')) {
            return (bool) preg_match($term, $sql);
        }

        // 普通字符串包含匹配
        return str_contains($sql, $term);
    }

    /**
     * 日志等级映射
     *
     * @param  array{
     *     slow: bool
     * }  $context
     */
    protected function resolveLogLevel(array $context): string
    {
        return $context['slow'] ? LogLevel::NOTICE : LogLevel::DEBUG;
    }

    /**
     * 是否记录 SQL
     */
    protected function shouldLog(QueryExecuted $event): bool
    {
        if ($this->isIgnored($event)) {
            return false;
        }

        return config('app.debug')
            || config('pin.logging.sql_logging')
            || $this->profile->isSlow($event);
    }
}
