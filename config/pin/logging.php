<?php

use Pin\Support\Str;

$isProduction = env('APP_ENV', 'production') === 'production';

return [
    /**
     * 是否启用 SQL 日志
     */
    'sql_logging' => env('LOG_SQL_ENABLED', ! $isProduction),

    /**
     * SQL 日志最大长度
     */
    'sql_max_length' => env('LOG_SQL_MAX_LENGTH', 10240),

    /**
     * API 响应日志配置
     */
    'response' => [

        /**
         * 是否启用响应日志
         */
        'enabled' => env('LOG_RESPONSE_ENABLED', ! $isProduction),

        /**
         * 排除记录的接口
         *
         * 指定无需记录响应日志的 URI / 路由名称。
         */
        'except' => Str::explode(env('LOG_RESPONSE_EXCEPT')),

        /**
         * 忽略 response.data 的接口
         *
         * 这些接口仍会记录日志，但不会记录 response.data 字段。
         */
        'ignore_response_data' => [
            ...Str::explode(env('LOG_RESPONSE_IGNORE_RESPONSE_DATA')),
            'captcha',
        ],

        /**
         * Response 最大长度
         */
        'max_length' => env('LOG_RESPONSE_MAX_LENGTH', 10240),

        /**
         * 是否记录请求体
         */
        'include_request_payload' => env(
            'LOG_RESPONSE_INCLUDE_REQUEST_PAYLOAD',
            ! $isProduction
        ),

        /**
         * 是否附带 SQL 执行记录
         */
        'include_sql' => env(
            'LOG_RESPONSE_INCLUDE_SQL',
            ! $isProduction
        ),

        /**
         * 慢请求阈值
         *
         * 支持两种格式：
         * - <= 10：按秒处理
         * - > 10：按毫秒处理
         */
        'slow_threshold' => env(
            'LOG_RESPONSE_SLOW_THRESHOLD',
            2
        ),
    ],

    /**
     * Stack Trace 配置
     *
     * 用于控制异常堆栈记录行为。
     */
    'stack_trace' => [

        /**
         * 是否启用 Stack Trace
         */
        'enabled' => env('LOG_STACK_TRACE_ENABLED', false),

        /**
         * 仅记录指定异常
         *
         * 为空表示允许所有异常。
         */
        'include_exceptions' => [],

        /**
         * 排除指定异常
         */
        'exclude_exceptions' => [],

        /**
         * 最大记录堆栈层级数量
         */
        'max_frames' => 10,

        /**
         * 仅保留匹配的 Frame
         *
         * 为空表示全部允许。
         */
        'include_frames' => [],

        /**
         * 排除指定 Frame
         *
         * 常用于过滤框架内部调用。
         */
        'exclude_frames' => [
            'Illuminate' => 'Illuminate',
        ],
    ],

    /**
     * 是否启用 `JSON_PRETTY_PRINT` 格式化日志输出
     */
    'json_pretty_print' => env('LOG_JSON_PRETTY_PRINT', false),
];
