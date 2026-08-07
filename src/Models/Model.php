<?php

declare(strict_types=1);

namespace Pin\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Pin\Models\Concerns\HasCache;
use Pin\Models\Concerns\HasEvents;
use Pin\Models\Concerns\HasMetadata;
use Pin\Models\Concerns\HasQueryable;
use Pin\Models\Queryable\Queryable;
use Pin\Models\Queryable\QueryableCondition;
use Pin\Pagination\Pagination;
use Pin\Support\Facades\RuntimeCache;
use Pin\Support\Json;
use Pin\Tree\Concerns\HasTree;

/**
 * Base Model
 *
 * 自定义 Eloquent 基类，扩展了 Laravel 原生 Model 功能：
 * - 自动添加缓存和事件支持（Cache, HasEvents）
 * - 提供树结构支持（Tree）和查询封装（Queryable、Aggregate）
 * - 支持动态排序（Sort）和分页（Pagination）
 * - JSON 字段序列化统一封装（Json::encode）
 * - 自定义时间字段序列化格式（Y-m-d H:i:s）
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
 * @method static Pagination pagination(?int $page = null, int|null $pageSize = null, array $columns = ['*'])
 *
 * @mixin Builder
 * @mixin HasTree
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    use HasCache, HasEvents, HasMetadata, HasQueryable;

    /**
     * 默认数据库连接
     */
    public const string CONNECTION_DEFAULT = 'default';

    /**
     * 允许批量赋值的字段，[] 表示全部可填充
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
    public function getPerPage()
    {
        if ($this->perPage !== null) {
            return parent::getPerPage();
        }

        return $this->perPage = Pagination::getPageSize();
    }

    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return RuntimeCache::rememberForever(static::class.'.table', fn () => parent::getTable());
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
        $connection = $this->getConnection();
        $connection->beginTransaction();

        return tap($callback($this), fn () => $connection->commit());
    }

    /**
     * 序列化日期字段
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
