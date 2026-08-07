<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

/**
 * ID 生成统一入口
 *
 * 支持多种生成器：Redis、Timestamp、Snowflake 或自定义扩展
 */
class Id
{
    /**
     * 生成一个或多个 ID
     *
     * @param  int  $count  生成数量
     * @param  string|IdGenerator|null  $generator  指定生成器，默认使用配置驱动
     * @return int|string|array 单个 ID 或 ID 列表
     */
    public static function generate(
        int $count = 1,
        string|IdGenerator|null $generator = null,
    ): array|int|string {
        $generator ??= config('pin.id-generator.default');

        return match ($generator) {
            IdGenerator::Redis,
            IdGenerator::Redis->value => IdGenerator::Redis->generate($count),

            IdGenerator::Timestamp,
            IdGenerator::Timestamp->value => IdGenerator::Timestamp->generate($count),

            IdGenerator::Snowflake,
            IdGenerator::Snowflake->value => IdGenerator::Snowflake->generate($count),

            default => app('pin.id.'.$generator)->generate($count),
        };
    }
}
