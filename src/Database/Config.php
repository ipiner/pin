<?php

declare(strict_types=1);

namespace Pin\Database;

/**
 * 数据库配置。
 */
class Config
{
    /**
     * 生成 MySQL 连接配置。
     *
     * @param  array  $options  覆盖默认配置
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

            // 慢查询阈值：不大于 10 按秒，其余按毫秒。
            'slow_threshold' => static::env($connection, 'SLOW_THRESHOLD', 2),
            ...$options,
        ];
    }

    /**
     * 读取连接环境变量。
     */
    protected static function env(string $connection, string $key, mixed $default = null): mixed
    {
        return env($connection.'_DB_'.$key, $default);
    }
}
