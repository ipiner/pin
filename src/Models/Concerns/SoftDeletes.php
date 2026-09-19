<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;
use Pin\Models\Scopes\SoftDeleting;

/**
 * 软删除
 */
trait SoftDeletes
{
    use BaseSoftDeletes;

    /**
     * 替换 Laravel 默认软删除作用域
     */
    public static function bootSoftDeletes(): void
    {
        static::addGlobalScope(new SoftDeleting());
    }

    /**
     * 初始化 deleted_at 字段类型转换
     */
    public function initializeSoftDeletes(): void
    {
        if (! isset($this->casts[$column = $this->getDeletedAtColumn()])) {
            $this->casts[$column] = $this->softDeletedAtValue(false) === null
                ? 'datetime'
                : 'timestamp';
        }
    }

    /**
     * 恢复软删除的模型
     */
    public function restore(): bool
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->exists = true;
        $this->forceFill($this->softDeletedValuesForUpdate(false));
        $result = $this->save();

        if ($result) {
            $this->fireModelEvent('restored', false);
        }

        return $result;
    }

    /**
     * 是否已软删除
     */
    public function trashed(): bool
    {
        return $this->{$this->getDeletedAtColumn()} !== $this->softDeletedAtValue(false);
    }

    /**
     * 获取软删除字段值
     *
     * @param  bool  $deleted  是否被标记为已删除
     */
    public function softDeletedAtValue(bool $deleted): int|string|null
    {
        return $deleted ? time() : 0;
    }

    /**
     * 获取软删除更新字段
     *
     * @param  bool  $deleted  是否被标记为已删除
     */
    public function softDeletedValuesForUpdate(bool $deleted): array
    {
        return [
            $this->getDeletedAtColumn() => $this->softDeletedAtValue($deleted),
        ];
    }

    /**
     * 执行软删除操作
     */
    protected function runSoftDelete(): void
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());
        $time = $this->freshTimestamp();
        $columns = $this->softDeletedValuesForUpdate(true);

        $this->forceFill($columns);

        if ($this->usesTimestamps() && $this->getUpdatedAtColumn()) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);
        $this->syncOriginalAttributes(array_keys($columns));
        $this->fireModelEvent('trashed', false);
    }
}
