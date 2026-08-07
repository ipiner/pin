<?php

/** @noinspection PhpIncompatibleReturnTypeInspection */

namespace Pin\Tests\Models\Models;

use App\Factories\AdminFactory;
use Pin\Models\Concerns\CacheItem;
use Pin\Models\Concerns\SoftDeletes;
use Pin\Models\Concerns\TimestampId;
use Pin\Models\Model;

/**
 * @property string $username
 * @property string $password
 */
class Admin extends Model
{
    use CacheItem, SoftDeletes, TimestampId;

    public static function createByFactory(array $attributes = []): static
    {
        return static::create(array_merge(AdminFactory::new()->definition(), $attributes));
    }
}
