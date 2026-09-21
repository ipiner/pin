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
     * 表结构构建器
     */
    protected Blueprint $table;

    /**
     * 获取连接名称
     */
    public function getConnection()
    {
        return $this->connection ?: config('database.default');
    }

    /**
     * 添加操作用户字段
     */
    protected function blameable(): void
    {
        $this->unsignedInteger('created_by', '创建用户id')->default(0);
        $this->unsignedInteger('updated_by', '更新用户id')->default(0);
    }

    /**
     * 添加软删除字段
     */
    protected function deleted(): void
    {
        $this->unsignedInteger('deleted_at', '删除时间戳')->default(0);
    }

    /**
     * 添加主键
     */
    protected function id(bool $autoIncrement = true, bool $bigint = false): ColumnDefinition
    {
        $column = $bigint
            ? $this->table->unsignedBigInteger('id')
            : $this->table->unsignedInteger('id');

        if ($autoIncrement) {
            return $column->autoIncrement()->comment('id|自增');
        }

        return $column->primary()->comment('id|由id生成器生成');
    }

    /**
     * 添加 JSON 字段
     */
    protected function json(
        string $column,
        string $comment,
        bool $nullable = true
    ): ColumnDefinition {
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
     * 添加多态字段及索引
     */
    protected function morphs(
        string $name,
        string $typeComment,
        string $idComment,
        bool $unique = true
    ): void {
        $typeColumn = "{$name}_type";
        $idColumn = "{$name}_id";

        $this->string($typeColumn, $typeComment);
        $this->unsignedBigInteger($idColumn, $idComment);

        if ($unique) {
            $this->table->unique([$typeColumn, $idColumn]);
        } else {
            $this->table->index([$typeColumn, $idColumn]);
        }

        $this->table->index($idColumn);
    }

    /**
     * 添加请求 ID 字段
     */
    protected function requestId(int $length = 36): ColumnDefinition
    {
        return $this->string('request_id', '请求id', $length);
    }

    /**
     * 获取表结构构建器
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
        $definition = $this->table->string($column, $length)->comment($comment);

        if ($allowEmpty) {
            $definition->default('');
        }

        return $definition;
    }

    /**
     * 添加可空时间戳字段
     */
    protected function timestamp(string $column, string $comment): ColumnDefinition
    {
        return $this->table->timestamp($column)->nullable()->comment($comment);
    }

    /**
     * 添加创建和更新时间字段
     */
    protected function timestamps(): void
    {
        $this->timestamp('created_at', '创建时间');
        $this->timestamp('updated_at', '更新时间');
    }

    /**
     * 添加无符号大整数字段
     */
    protected function unsignedBigInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedBigInteger($column)->comment($comment);
    }

    /**
     * 添加无符号整数字段
     */
    protected function unsignedInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedInteger($column)->comment($comment);
    }

    /**
     * 添加无符号小整数字段
     */
    protected function unsignedSmallInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedSmallInteger($column)->comment($comment);
    }

    /**
     * 添加无符号 tinyint 字段
     */
    protected function unsignedTinyInteger(string $column, string $comment): ColumnDefinition
    {
        return $this->table->unsignedTinyInteger($column)->comment($comment);
    }

    /**
     * 绑定表结构构建器
     */
    protected function useTable(Blueprint $table): void
    {
        $this->table = $table;
    }

    /**
     * 添加数据版本字段
     */
    protected function version(): ColumnDefinition
    {
        return $this->unsignedInteger('v', '数据版本号')->default(1);
    }
}
