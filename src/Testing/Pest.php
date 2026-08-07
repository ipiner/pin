<?php

declare(strict_types=1);

namespace Pin\Testing;

/**
 * Pest 测试增强
 *
 * @codeCoverageIgnore
 */
class Pest
{
    /**
     * 启动 Pest 测试
     */
    public static function boot(): void
    {
        require_once __DIR__.'/Pest/descriptions.php';
    }
}
