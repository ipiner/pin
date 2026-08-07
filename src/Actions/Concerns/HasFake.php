<?php

declare(strict_types=1);

namespace Pin\Actions\Concerns;

use Pin\Faker\Fake;

/**
 * 为 Action 提供 fake 数据生成能力
 */
trait HasFake
{
    /**
     * 生成 fake 数据
     *
     * {@see fakeData} 的静态调用
     *
     * @param  array  $attributes  覆盖或添加字段
     */
    public static function fake(array $attributes = []): array
    {
        return app(static::class)->fakeData($attributes);
    }

    /**
     * 根据 validationRules 生成 fake 数据
     *
     * @param  array  $attributes  覆盖或添加字段
     */
    public function fakeData(array $attributes = []): array
    {
        return [
            ...Fake::generate($this->validationRules()),
            ...$attributes,
        ];
    }
}
