<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Illuminate\Database\Eloquent\Builder;
use Override;
use Pin\Models\Model;

/**
 * 唯一性验证。
 *
 * @template TModel of Model
 */
class Unique extends ValidationRule
{
    protected string $message = 'validation.unique';

    /**
     * 附加查询条件
     *
     * @var array<string, array{string, string, mixed}>
     */
    protected array $wheres = [];

    /**
     * @param  class-string<TModel>  $modelClass  目标模型类
     */
    public function __construct(protected string $modelClass)
    {
    }

    /**
     * 判断字段值是否已存在。
     */
    public function exists(string $attribute, mixed $value): bool
    {
        return $this->buildQuery($attribute, $value)->exists();
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
     * 验证唯一性。
     */
    #[Override]
    protected function handle(string $attribute, mixed $value): bool
    {
        return ! $this->exists($attribute, $value);
    }

    /**
     * 设置查询条件。
     *
     * @param  string|array{string, string, mixed}  $column
     */
    public function where(string|array $column, mixed $value = null): static
    {
        $where = is_array($column) ? $column : [$column, '=', $value];
        $this->wheres[$where[0]] = $where;

        return $this;
    }

    /**
     * 设置不等于条件。
     */
    public function whereNot(string $column, mixed $value): static
    {
        return $this->where([$column, '!=', $value]);
    }

    /**
     * 构建唯一性查询。
     *
     * @return Builder<TModel>
     */
    protected function buildQuery(string $attribute, mixed $value): Builder
    {
        $query = $this->modelClass::query()->where($attribute, $value);

        foreach ($this->wheres as $where) {
            $query->where(...$where);
        }

        return $query;
    }
}
