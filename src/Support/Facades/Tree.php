<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

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
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.tree';
    }
}
