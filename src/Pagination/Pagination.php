<?php

declare(strict_types=1);

namespace Pin\Pagination;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;
use JsonSerializable;
use Override;

/**
 * 分页结果
 *
 * @template TItem
 */
class Pagination implements JsonSerializable
{
    /**
     * 分页实例。
     *
     * @var LengthAwarePaginator<array-key, TItem>
     */
    public LengthAwarePaginator $paginator;

    /**
     * 获取允许的分页大小。
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
        return (int) config('pin.pagination.default_page_size', 15);
    }

    /**
     * 获取当前请求的分页大小
     */
    public static function getPageSize(?int $pageSize = null): int
    {
        $pageSize ??= Request::integer(config('pin.pagination.page_size_name', 'page_size'));

        if ($pageSize <= 0) {
            return static::getDefaultPageSize();
        }

        $availablePageSizes = static::getAvailablePageSizes();

        return ! $availablePageSizes || in_array($pageSize, $availablePageSizes, true)
            ? $pageSize
            : static::getDefaultPageSize();
    }

    /**
     * 创建分页结果
     */
    public static function make(LengthAwarePaginator $paginator): static
    {
        $pagination = app(static::class);
        $pagination->paginator = $paginator;

        return $pagination;
    }

    /**
     * 序列化分页结果
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 转换为数组。
     *
     * @param  bool|string|Closure  $withItems  是否保留数据、资源类名或转换回调
     * @return array{
     *     total:int,
     *     total_page:int,
     *     items:mixed
     * }
     */
    public function toArray(bool|string|Closure $withItems = true): array
    {
        $total = $this->paginator->total();

        return [
            // 总数
            'total' => $total,
            /**
             * 总页数
             *
             * @var int
             */
            'total_page' => $total ? $this->paginator->lastPage() : 0,
            /**
             * 数据
             *
             * @var TItem[]
             */
            'items' => $this->resolveItems(
                $withItems === false ? [] : $this->paginator->items(),
                $withItems
            ),
        ];
    }

    /**
     * 转换分页数据
     */
    protected function resolveItems(array $items, bool|string|Closure $withItems = true): mixed
    {
        return match (true) {
            $withItems instanceof Closure => $withItems($items),
            is_string($withItems) => array_map(
                static fn ($item) => new $withItems($item)->resolve(),
                $items
            ),
            $withItems => $items,
            default => null,
        };
    }
}
