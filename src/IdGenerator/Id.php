<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

/**
 * ID 生成统一入口
 */
class Id
{
    /**
     * 生成一个或多个 ID
     *
     * @param  int  $count  生成数量
     * @param  string|IdGenerator|null  $generator  生成器名称或枚举
     * @return int|string|list<int|string>
     */
    public static function generate(
        int $count = 1,
        string|IdGenerator|null $generator = null,
    ): array|int|string {
        $generator ??= config('pin.id-generator.default');

        if ($generator instanceof IdGenerator) {
            return $generator->generate($count);
        }

        return app('pin.id.'.$generator)->generate($count);
    }
}
