<?php

declare(strict_types=1);

namespace Pin\Database;

/**
 * 数据库配置生成器（ENV → Laravel config）。
 */
class Config
{
    /**
     * 生成 MySQL 数据库配置
     *
     * @param  string  $connection  连接名称（如 default / report）
     * @param  array  $options  额外配置（用于覆盖默认值）
     */
    public static function mysql(string $connection, array $options = []): array
    {
        $connection = strtoupper($connection);

        return [
            'driver' => 'mysql',
            'url' => static::env($connection, 'URL'),
            'host' => static::env($connection, 'HOST'),
            'port' => static::env($connection, 'PORT', 3306),
            'database' => static::env($connection, 'DATABASE'),
            'username' => static::env($connection, 'USERNAME'),
            'password' => static::env($connection, 'PASSWORD'),
            'unix_socket' => static::env($connection, 'SOCKET', ''),
            'charset' => static::env($connection, 'CHARSET', 'utf8mb4'),
            'collation' => static::env($connection, 'COLLATION'),
            'prefix' => static::env($connection, 'PREFIX', ''),
            'strict' => static::env($connection, 'STRICT_MODE', true),
            'engine' => static::env($connection, 'ENGINE'),
            'timezone' => static::env($connection, 'TIMEZONE') ?: null,

            /**
             * 慢查询阈值
             *
             * 1. 秒级配置（推荐）：<= 10 的数值视为“秒”
             * 2. 毫秒级配置 ：> 10 的数值视为“毫秒”
             */
            'slow_threshold' => static::env($connection, 'SLOW_THRESHOLD', 2),
            ...$options,
        ];
    }

    /**
     * 读取环境变量
     */
    protected static function env(string $connection, string $key, mixed $default = null): mixed
    {
        return env($connection.'_DB_'.$key, $default);
    }
}
