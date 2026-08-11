<?php

declare(strict_types=1);

namespace Pin\Pagination;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;
use JsonSerializable;

/**
 * 分页结果包装类
 *
 * @template TItem
 */
class Pagination implements JsonSerializable
{
    /**
     * Laravel 分页实例
     */
    public LengthAwarePaginator $paginator;

    /**
     * 获取允许的分页大小列表
     *
     * @return array<int>
     */
    public static function getAvailablePageSizes(): array
    {
        return config('pin.pagination.available_page_sizes', []);
    }

    /**
     * 获取默认分页大小
     */
    public static function getDefaultPageSize(): int
    {
        return config('pin.pagination.default_page_size', 15);
    }

    /**
     * 获取当前请求的分页大小
     */
    public static function getPageSize(?int $pageSize = null): int
    {
        $pageSize = $pageSize ?? Request::integer(config('pin.pagination.page_size_name', 'page_size'));

        // 未限制 page size 时，允许任意正整数
        if (! static::getAvailablePageSizes() && $pageSize > 0) {
            return $pageSize;
        }

        // 限制在白名单范围内，否则使用默认值
        return in_array($pageSize, static::getAvailablePageSizes(), true)
            ? $pageSize
            : static::getDefaultPageSize();
    }

    /**
     * 创建 Pagination 实例
     *
     * @param  iterable<int, mixed>|array<int, mixed>  $items  分页数据
     * @param  int|null  $total  总记录数，默认自动使用 `count($items)`
     * @param  int|null  $perPage  每页数量，默认 `pin.pagination.default_page_size`
     */
    public static function make(LengthAwarePaginator $paginator): static
    {
        $pagination = app(static::class);
        $pagination->paginator = $paginator;

        return $pagination;
    }

    /**
     * 实现 `JsonSerializable` 接口
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 转换为数组结构
     *
     * @return array{
     *     total:int,
     *     total_page:int,
     *     items:TItem[]
     * }
     */
    public function toArray(bool|string|Closure $withItems = true): array
    {
        $items = $this->paginator->items();

        return [
            // 总数
            'total' => $this->paginator->total(),
            /**
             * 总页数
             *
             * @var int
             */
            'total_page' => $this->paginator->total() ? $this->paginator->lastPage() : 0,
            /**
             * 数据
             *
             * @var TItem[]
             */
            'items' => $this->resolveItems($items, $withItems),
        ];
    }

    /**
     * 处理 items 数据
     */
    protected function resolveItems(array $items, bool|string|Closure $withItems = true): mixed
    {
        return match (true) {
            // 自定义处理
            $withItems instanceof Closure => $withItems($items),

            // Resource 类转换
            // 如果 Resource 内部有数据库查询，需要 resolve() 才能触发 SQL 日志
            is_string($withItems) => array_map(
                fn ($item) => new $withItems($item)->resolve(),
                $items
            ),

            // 原样返回
            $withItems => $items,

            // 不返回 items
            default => null,
        };
    }
}
