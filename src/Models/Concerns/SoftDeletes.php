<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Models\Scopes\SoftDeleting;

/**
 * 软删除
 *
 * 使用 `deleted_at unsigned int` 字段标记记录是否被删除
 */
trait SoftDeletes
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

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
    public function initializeSoftDeletes()
    {
        if (! isset($this->casts[$column = $this->getDeletedAtColumn()])) {
            $this->casts[$column] = $this->softDeletedAtValue(false) === null
                ? 'datetime'
                : 'timestamp';
        }
    }

    /**
     * 恢复被软删除的模型
     *
     * @return bool
     */
    public function restore()
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false; // @codeCoverageIgnore
        }

        $this->exists = true;
        $columns = $this->softDeletedValuesForUpdate(false);
        foreach ($columns as $column => $value) {
            $this->{$column} = $value;
        }
        $result = $this->update($columns);
        $this->fireModelEvent('restored', false);

        return $result;
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
     * 生成用于 update 的软删除字段数组
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

        foreach ($columns as $column => $value) {
            $this->{$column} = $value;
        }

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->usesTimestamps() && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);
        $this->syncOriginalAttributes(array_keys($columns));
        $this->fireModelEvent('trashed', false);
    }
}
