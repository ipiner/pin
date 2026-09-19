<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Godruoyi\Snowflake\Snowflake;
use Override;

/**
 * Snowflake ID 生成器
 */
class SnowflakeId implements IdGeneratorInterface
{
    /**
     * Snowflake 实例
     */
    protected Snowflake $snowflake;

    /**
     * @param  array{data_center: int, worker_id: int, start_timestamp: int}  $config  配置项
     */
    public function __construct(array $config)
    {
        $this->snowflake = new Snowflake($config['data_center'], $config['worker_id'])
            ->setStartTimeStamp($config['start_timestamp'] * 1000);
    }

    /**
     * 生成一个或多个 ID。
     *
     * @param  int  $count  生成数量
     * @return string|list<string>
     */
    #[Override]
    public function generate(int $count = 1): array|string
    {
        if ($count === 1) {
            return $this->snowflake->id();
        }

        $ids = [];

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $this->snowflake->id();
        }

        return $ids;
    }

    /**
     * 解析 ID，timestamp 为相对起始时间的毫秒数。
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
