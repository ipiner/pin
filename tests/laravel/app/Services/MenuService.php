<?php

namespace App\Services;

use App\Models\Menu;
use Pin\Tree\ModelService;

class MenuService extends ModelService
{
    public string $resourceName = '菜单';

    public function __construct()
    {
        parent::__construct(Menu::class);
    }
}
