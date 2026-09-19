<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Pin\Models\Model;
use Pin\Support\Arr;

/**
 * 保存操作
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
     * 保存前处理
     *
     * @param  TModel|null  $model
     * @param  array<string, mixed>  $data
     */
    protected function saving($model, array &$data): void
    {
        if ($this->shouldConvertNullToEmptyString()) {
            $data = Arr::nullToEmptyString($data);
        }
    }

    /**
     * 保存后处理
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     */
    protected function saved($model, array $data): void
    {
    }

    /**
     * 是否将 `null` 转为空字符串
     */
    protected function shouldConvertNullToEmptyString(): bool
    {
        return $this->convertNullToEmptyString
            && $this->context('convertNullToEmptyString') !== false;
    }
}
