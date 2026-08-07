<?php

declare(strict_types=1);

namespace Pin\Testing;

/**
 * 测试环境专用的 Application 实现
 *
 * 该类继承自基础的 \Pin\Application，用于在测试启动时对配置进行补充或覆盖
 */
class Application extends \Pin\Application
{
    /**
     * 配置加载完成后的回调方法
     */
    public function loadedConfiguration(): void
    {
        parent::loadedConfiguration();

        // 如果没有定义 testing 数据库连接，则自动创建
        if (! $this['config']->get('database.connections.testing')) {
            $this['config']->set('database.connections.testing', [
                // 使用 SQLite 驱动（轻量、无需服务）
                'driver' => 'sqlite',

                // 使用内存数据库（测试结束即销毁）
                'database' => ':memory:',

                // 是否开启外键约束（测试中通常关闭以避免约束干扰）
                'foreign_key_constraints' => false,
            ]);
        }
    }
}
