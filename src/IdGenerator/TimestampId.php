<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Override;

/**
 * 基于时间差和微秒尾码生成整数 ID
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
     * 生成一个或多个 ID。
     *
     * @param  int  $count  生成数量
     * @return int|list<int>
     */
    #[Override]
    public function generate(int $count = 1): array|int
    {
        if ($count === 1) {
            return $this->next();
        }

        $ids = [];

        for ($i = 0; $i < $count; $i++) {
            $ids[] = $this->next();
        }

        return $ids;
    }

    /**
     * 生成单个 ID
     */
    protected function next(): int
    {
        $elapsed = (time() - $this->startTimestamp) * 100000;

        return $elapsed + hexdec(substr(uniqid(), -4));
    }
}
