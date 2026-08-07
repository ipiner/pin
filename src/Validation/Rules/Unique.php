<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Unique 唯一性验证规则（增强版）
 *
 * 基于 Eloquent Model 的唯一性校验规则，用于替代 Laravel 原生 unique 规则
 *
 * @template TModel of Model
 */
class Unique extends ValidationRule
{
    /**
     * 附加查询条件
     *
     * @var array<string, array{0:string,1:string,2:mixed}>
     */
    protected array $wheres = [];

    /**
     * 构造函数
     *
     * @param  class-string<TModel>  $modelClass  目标模型类
     */
    public function __construct(protected $modelClass)
    {
        $this->message('validation.unique');
    }

    /**
     * 判断指定值是否已存在
     *
     * @param  string  $attribute  字段名
     * @param  mixed  $value  字段值
     */
    public function exists(string $attribute, mixed $value): bool
    {
        return $this->buildQuery($attribute, $value)->select('id')->first() !== null;
    }

    /**
     * 忽略指定主键 ID
     */
    public function ignore(int $id): static
    {
        if ($id > 0) {
            $this->whereNot('id', $id);
        }

        return $this;
    }

    /**
     * 执行验证
     */
    protected function handle(string $attribute, mixed $value): bool
    {
        return ! $this->exists($attribute, $value);
    }

    /**
     * 添加 where 条件
     *
     * @param  string|array{0:string,1:string,2:mixed}  $column
     */
    public function where(string|array $column, mixed $value = null): static
    {
        if (is_array($column)) {
            $this->wheres[$column[0]] = $column;
        } else {
            $this->wheres[$column] = [$column, '=', $value];
        }

        return $this;
    }

    /**
     * 添加 where != 条件
     */
    public function whereNot(string $column, mixed $value): static
    {
        $this->wheres[$column] = [$column, '!=', $value];

        return $this;
    }

    /**
     * 构建唯一性查询
     *
     * @param  string  $attribute  字段名
     * @param  mixed  $value  字段值
     */
    protected function buildQuery(string $attribute, mixed $value): Builder
    {
        $query = $this->modelClass::where($attribute, $value);

        foreach ($this->wheres as [$column, $operator, $value]) {
            $query->where($column, $operator, $value);
        }

        return $query;
    }
}
