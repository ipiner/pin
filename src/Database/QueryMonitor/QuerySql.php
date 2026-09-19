<?php

declare(strict_types=1);

namespace Pin\Database\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;

/**
 * SQL 格式化。
 */
class QuerySql
{
    /**
     * 获取绑定参数后的 SQL。
     */
    public static function raw(QueryExecuted $event): string
    {
        return static::truncate($event->toRawSql());
    }

    /**
     * 截断 SQL。
     */
    public static function truncate(string $sql): string
    {
        $maxLength = (int) config('pin.logging.sql_max_length', 10240);

        if (strlen($sql) <= $maxLength) {
            return $sql;
        }

        $length = Str::length($sql, 'UTF-8');

        if ($length <= $maxLength) {
            return $sql;
        }

        return Str::substr($sql, 0, $maxLength).'(...'.($length - $maxLength).')';
    }
}
