<?php

declare(strict_types=1);

use Pin\Tree\TreeServiceProvider;

it('declares provided services', function () {
    $provider = new TreeServiceProvider($this->app);
    expect($provider->provides())->toBe([
        'pin.tree',
        'pin.tree.checker',
        'pin.tree.filter',
        'pin.tree.sorter',
    ]);
});
