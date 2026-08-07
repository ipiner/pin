<?php

use Pin\IdGenerator\IdGenerator;
use Pin\IdGenerator\TimestampId;

return [
    /**
     * 默认 ID 生成器
     *
     * 可选值：
     * - IdGenerator::Timestamp
     * - IdGenerator::Redis
     * - IdGenerator::Snowflake
     */
    'default' => IdGenerator::Timestamp,

    /**
     * Timestamp ID 配置
     */
    'timestamp' => [
        /**
         * ID 起始时间戳，可缩短最终 ID 长度
         */
        'start_timestamp' => TimestampId::START_TIMESTAMP,
    ],

    /**
     * Redis 分布式 ID 配置
     */
    'redis' => [
        /**
         * 生成器名称
         */
        'name' => 'default',

        /**
         * 是否启用分布式锁
         */
        'use_lock' => false,
    ],

    /**
     * Snowflake 雪花算法配置
     *
     * - data_center
     *   数据中心 ID
     *
     * - worker_id
     *   机器节点 ID
     *
     * - start_timestamp
     *   起始时间戳
     *   用于缩短 ID 长度
     */
    'snowflake' => [
        /**
         * 数据中心 ID
         *
         * 默认随机
         */
        'data_center' => -1,
        /**
         * 机器节点 ID
         *
         * 默认随机
         */
        'worker_id' => -1,
        /**
         * 起始时间戳
         */
        'start_timestamp' => 1714492800,
    ],
];
