<?php

declare(strict_types=1);

namespace Pin\Testing;

/**
 * Pest 测试辅助。
 *
 * @codeCoverageIgnore
 */
class Pest
{
    /**
     * 加载测试描述函数。
     */
    public static function boot(): void
    {
        require_once __DIR__.'/Pest/descriptions.php';
    }
}
