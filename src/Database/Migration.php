<?php

declare(strict_types=1);

namespace Pin\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Schema;

/**
 * 数据库迁移基类
 */
class Migration extends \Illuminate\Database\Migrations\Migration
{
    /**
     * 当前操作的 Blueprint 实例（表结构构建器）
     */
    protected Blueprint $table;

    /**
     * 获取当前数据库连接
     */
    public function getConnection()
    {
        return $this->connection ?: config('database.default');
    }

    /**
     * 操作用户字段
     *
     * 用于记录数据的创建用户与更新用户
     */
    protected function blameable(): void
    {
        $this->unsignedInteger('created_by', '创建用户id')->default(0);
        $this->unsignedInteger('updated_by', '更新用户id')->default(0);
    }

    /**
     * 添加软删除字段（deleted_at）
     */
    protected function deleted(): void
    {
        $this->unsignedInteger('deleted_at', '删除时间戳')->default(0);
    }

    /**
     * 添加主键 id
     */
    protected function id(bool $autoIncrement = true, bool $bigint = false): ColumnDefinition
    {
        $col = $bigint ? $this->table->unsignedBigInteger('id') : $this->table->unsignedInteger('id');

        if ($autoIncrement) {
            return $col->autoIncrement()->comment('id|自增');
        }

        return $col->primary()->comment('id|由id生成器生成');
    }

    /**
     * json字段
     */
    protected function json(string $column, string $comment, bool $nullable = true): ColumnDefinition
    {
        return $this->table->json($column)->nullable($nullable)->comment($comment);
    }

    /**
     * 生成表或字段注释
     */
    protected function makeComment(string $comment, string $creator): string
    {
        return $comment.'|'.date('Ymd').'|'.$creator;
    }

    /**
     * 多态字段（morphs）
     *
     * 生成：
     *   {name}_type
     *   {name}_id
     *
     * 并自动创建索引
     */
    protected function morphs(string $name, string $typeComment, $idComment, bool $unique = true): void
    {
        $this->string("{$name}_type", $typeComment);
        $this->unsignedBigInteger("{$name}_id", $idComment);

        if ($unique) {
            $this->table->unique(["{$name}_type", "{$name}_id"]);
        } else {
            $this->table->index(["{$name}_type", "{$name}_id"]);
        }

        // 单独索引 id（提升查询性能）
        $this->table->index("{$name}_id");
    }

    /**
     * 请求id字段（request_id）
     */
    protected function requestId($length = 36): ColumnDefinition
    {
        return $this->string('request_id', '请求id', $length);
    }

    /**
     * 获取 Schema Builder
     */
    protected function schema(): Builder
    {
        return Schema::connection($this->getConnection());
    }

    /**
     * 添加字符串字段
     */
    protected function string(
        string $column,
        string $comment,
        ?int $length = null,
        bool $allowEmpty = false
    ): ColumnDefinition {
        $definition = $this->table->string($column, $length)
            ->comment($comment);

        if ($allowEmpty) {
            $definition->default('');
        }

        return $definition;
    }

    /**
     * 创建/更新时间字段（nullable）
     */
    protected function timestamp(string $column, string $comment): ColumnDefinition
    {
        return $this->table->timestamp($column)
            ->nullable()
            ->comment($comment);
    }

    /**
     * 添加 created_at / updated_at
     */
    protected function timestamps(): void
    {
        $this->timestamp('created_at', '创建时间');
        $this->timestamp('updated_at', '更新时间');
    }

    /**
     * 字段封装：unsigned bigint
     */
    protected function unsignedBigInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedBigInteger($column)->comment($comment);
    }

    /**
     * 字段封装：unsigned int
     */
    protected function unsignedInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedInteger($column)->comment($comment);
    }

    /**
     * 字段封装：unsigned smallint
     */
    protected function unsignedSmallInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedSmallInteger($column)->comment($comment);
    }

    /**
     * 字段封装：unsigned tinyint
     */
    protected function unsignedTinyInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedTinyInteger($column)->comment($comment);
    }

    /**
     * 绑定 Blueprint
     */
    protected function useTable(Blueprint $table): void
    {
        $this->table = $table;
    }

    /**
     * 数据版本号
     */
    protected function version(): ColumnDefinition
    {
        return $this->table->unsignedInteger('v')->default(1)->comment('数据版本号');
    }
}
