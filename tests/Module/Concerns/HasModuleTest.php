<?php

declare(strict_types=1);

use Pin\Module\ModuleInspector;

it('recognizes module namespaces', function (string $class, ?string $module) {
    expect((new ModuleInspector($class))->module())->toBe([
        'name' => $module,
        'namespace' => $module ? 'App\\Modules\\'.$module : null,
    ]);
})->with([
    ['App\\Modules\\Product\\Category\\CategoryService', 'Product'],
    ['App\\Routes\\Product\\CategoryRoute', 'Product'],
    ['App\\Routes\\CategoryRoute', null],
    ['App\\ModulesExtra\\Product\\ProductService', null],
    ['App\\Modules', null],
    ['Vendor\\Routes\\Product\\ProductRoute', null],
    ['App\\Services\\Product\\ProductRoute', null],
]);
