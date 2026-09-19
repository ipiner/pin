<?php

declare(strict_types=1);

namespace Pin\Testing;

use Override;
use Pin\Application as BaseApplication;

/**
 * 测试应用。
 */
class Application extends BaseApplication
{
    /**
     * 补充测试数据库配置。
     */
    #[Override]
    public function loadedConfiguration(): void
    {
        parent::loadedConfiguration();

        $config = $this['config'];

        if ($config->get('database.connections.testing')) {
            return;
        }

        $config->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => false,
        ]);
    }
}
