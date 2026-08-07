<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Godruoyi\Snowflake\Snowflake;

/**
 * 基于 Twitter Snowflake 算法生成分布式唯一 ID
 */
class SnowflakeId implements IdGeneratorInterface
{
    /**
     * @var Snowflake Snowflake 实例
     */
    protected Snowflake $snowflake;

    /**
     * @param  array{data_center: int, worker_id: int, start_timestamp: int}  $config  配置项
     */
    public function __construct(array $config)
    {
        $this->snowflake = new Snowflake($config['data_center'], $config['worker_id']);
        $this->snowflake->setStartTimeStamp($config['start_timestamp'] * 1000);
    }

    /**
     * 生成一个或多个 ID
     *
     * @param  int  $count  生成数量
     * @return array|string 单个 ID 或 ID 列表
     */
    public function generate(int $count = 1): array|string
    {
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $ids[] = $this->snowflake->id();
        }

        return $count === 1 ? $ids[0] : $ids;
    }

    /**
     * 解析 Snowflake ID
     *
     * @param  int|string  $id  要解析的 ID
     * @return array{
     *     timestamp: int,
     *     sequence: int,
     *     workerid: int,
     *     datacenter: int,
     * }
     */
    public function parseId(int|string $id): array
    {
        return $this->snowflake->parseId((string) $id, true);
    }
}
