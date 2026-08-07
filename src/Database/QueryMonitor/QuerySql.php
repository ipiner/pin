<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;

/**
 * SQL 输出工具（格式化 + 截断）。
 */
class QuerySql
{
    /**
     * 获取 SQL（raw + 截断）。
     */
    public static function raw(QueryExecuted $event): string
    {
        $sql = $event->toRawSql();

        return static::truncate($sql);
    }

    /**
     * 截断 SQL（避免日志过大）。
     */
    public static function truncate(string $sql): string
    {
        $length = Str::length($sql, 'UTF-8');
        $maxLength = config('pin.logging.sql_max_length', 10240);
        $exceedLength = $length - $maxLength;

        if ($exceedLength <= 0) {
            return $sql;
        }

        return Str::substr($sql, 0, $maxLength).'(...'.$exceedLength.')';
    }
}
