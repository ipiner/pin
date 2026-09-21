<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Override;

/**
 * @method static array check(Collection $models)
 * @method static Collection filter(Collection $models, callable $predicate)
 * @method static Collection sort(Collection $items)
 *
 * @see \Pin\Tree\Tree
 */
class Tree extends Facade
{
    /**
     * 获取服务名称
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'pin.tree';
    }
}
