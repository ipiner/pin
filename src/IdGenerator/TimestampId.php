<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

/**
 * 基于时间差生成趋势递增的整数 ID
 *
 * - 趋势递增
 * - 16 位以内整数
 * - bigint 安全
 * - 浏览器 / JavaScript 安全整数
 * - 无状态实现，适用于 FPM / Octane / Swoole
 * - 不依赖 Redis、数据库或分布式协调
 *
 * ID 结构：
 * [时间差][尾部扰动]
 *
 * 如需以下能力：
 * - 严格全局递增
 * - 分布式唯一
 * - 超高并发安全
 *
 * 建议使用：
 * - {@see SnowflakeId}
 * - {@see RedisId}
 */
class TimestampId implements IdGeneratorInterface
{
    /**
     * 默认起始时间戳（2026-05-01）
     */
    public const int START_TIMESTAMP = 1777593600;

    /**
     * @param  int  $startTimestamp  起始时间戳
     */
    public function __construct(protected int $startTimestamp = self::START_TIMESTAMP)
    {
    }

    /**
     * 生成一个或多个 ID
     *
     * @param  int  $count  生成数量
     * @return int|int[] 单个 ID 或 ID 列表
     */
    public function generate(int $count = 1): array|int
    {
        $ids = [];

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $this->next();
        }

        return $count === 1 ? $ids[0] : $ids;
    }

    /**
     * 生成单个 ID
     *
     * 尾部保留 5 位随机扰动，降低同时间窗口冲突概率
     */
    protected function next(): int
    {
        $elapsed = (time() - $this->startTimestamp) * 100000;

        return $elapsed + hexdec(substr(uniqid(), -4));
    }
}
