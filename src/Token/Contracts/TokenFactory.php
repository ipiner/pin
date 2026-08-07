<?php

declare(strict_types=1);

namespace Pin\Token\Contracts;

/**
 * Token Factory 接口
 */
interface TokenFactory
{
    /**
     * 获取 Token Driver 实例
     */
    public function driver(): TokenDriver;
}
