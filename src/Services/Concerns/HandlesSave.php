<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Pin\Models\Model;
use Pin\Support\Arr;

/**
 * Trait HandlesSave
 *
 * 为 Service 提供统一的保存前 / 保存后扩展点，
 * 用于封装 create / update 生命周期中的公共逻辑。
 *
 * @template TModel of Model
 */
trait HandlesSave
{
    /**
     * 是否将 `null` 转为空字符串
     */
    protected bool $convertNullToEmptyString = true;

    /**
     * 保存前生命周期钩子
     *
     * @param  TModel|null  $model  当前模型实例（create 时可能为 null）
     * @param  array<string, mixed>  $data  待保存数据（引用传递）
     */
    protected function saving($model, array &$data): void
    {
        if ($this->shouldConvertNullToEmptyString()) {
            $data = Arr::nullToEmptyString($data);
        }
    }

    /**
     * 保存后生命周期钩子
     *
     * @param  TModel  $model  已保存模型
     * @param  array<string, mixed>  $data  保存数据
     */
    protected function saved($model, array $data): void
    {
    }

    /**
     * 是否将 `null` 转为空字符串
     */
    protected function shouldConvertNullToEmptyString(): bool
    {
        if ($this->context('convertNullToEmptyString') === false) {
            return false;
        }

        return $this->convertNullToEmptyString;
    }
}
