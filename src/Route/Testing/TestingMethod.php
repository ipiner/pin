<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

/**
 * 自动化测试方法
 */
enum TestingMethod: string
{
    /**
     * 创建断言
     */
    case Created = 'created';
    /**
     * 更新断言
     */
    case Updated = 'updated';
    /**
     * 删除断言
     */
    case Deleted = 'deleted';
    /**
     * 分页响应断言
     */
    case Paginated = 'paginated';
    /**
     * 一般成功断言
     */
    case Successful = 'successful';
}
