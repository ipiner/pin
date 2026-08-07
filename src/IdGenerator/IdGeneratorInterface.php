<?php

namespace Pin\IdGenerator;

/**
 * ID 生成器接口
 */
interface IdGeneratorInterface
{
    /**
     * 生成 ID
     *
     * @param  int  $count  要生成的 ID 数量，默认为 1
     * @return int|int[]|string[] 返回单个 ID 或 ID 数组
     * @return array|int|string
     */
    public function generate(int $count = 1);
}
