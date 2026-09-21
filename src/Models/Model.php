<?php

declare(strict_types=1);

namespace Pin\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Override;
use Pin\Models\Concerns\HasCache;
use Pin\Models\Concerns\HasEvents;
use Pin\Models\Concerns\HasMetadata;
use Pin\Models\Concerns\HasQueryable;
use Pin\Models\Queryable\Queryable;
use Pin\Models\Queryable\QueryableCondition;
use Pin\Pagination\Pagination;
use Pin\Tree\Concerns\HasTree;

/**
 * 模型基类
 *
 * @property int|null $id
 * @property int|null $v
 *
 * @method static static create(array $data)
 * @method static Builder|static addSelectCount(string $column = '*', string $alias = 'total')
 * @method static Builder|static addSelectSum(string $column, string $alias = null)
 * @method static Builder|static addSelectAvg(string $column, string $alias = null)
 * @method static Builder|static addSelectMax(string $column, string $alias = null)
 * @method static Builder|static addSelectMin(string $column, string $alias = null)
 * @method static Builder|static queryable(Queryable|QueryableCondition|array $queryable)
 * @method static Builder|static sort(array|string|null $value, array|string $allows)
 * @method static Pagination pagination(?int $page = null, ?int $pageSize = null, array $columns = ['*'])
 *
 * @mixin Builder
 * @mixin HasTree
 */
class Model extends BaseModel
{
    use HasCache;
    use HasEvents;
    use HasMetadata;
    use HasQueryable;

    /**
     * 默认数据库连接
     */
    public const string CONNECTION_DEFAULT = 'default';

    /**
     * 禁止批量赋值的字段
     *
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * 每页默认分页数量
     */
    protected $perPage = null;

    /**
     * 获取每页分页数量
     */
    #[Override]
    public function getPerPage(): int
    {
        return $this->perPage ??= Pagination::getPageSize();
    }

    /**
     * 获取表名
     */
    #[Override]
    public function getTable(): string
    {
        return $this->table ??= parent::getTable();
    }

    /**
     * 执行事务
     *
     * @template TReturn
     *
     * @param  callable(static): TReturn  $callback
     * @return TReturn
     */
    public function transaction(callable $callback)
    {
        return $this->getConnection()->transaction(fn () => $callback($this));
    }

    /**
     * 序列化日期字段
     */
    #[Override]
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
