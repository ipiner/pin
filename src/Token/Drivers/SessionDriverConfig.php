<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Pin\Support\DataBag;

/**
 * Session 驱动配置。
 *
 * @property int $expires 有效期（秒）
 * @property string $cache_prefix 缓存键前缀
 * @property int $max_age 最大有效期（秒）
 * @property int $refresh_before 自动续期阈值（秒）
 */
class SessionDriverConfig extends DataBag
{
}
