<?php

declare(strict_types=1);

namespace Pin\Database;

/**
 * 使用 Pin 自定义 stub 的迁移文件创建器。
 */
class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    /**
     * MigrationCreator constructor.
     */
    public function __construct()
    {
        parent::__construct(app('files'), __DIR__.'/stubs');
    }
}
