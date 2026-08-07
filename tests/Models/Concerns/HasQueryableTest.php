<?php

declare(strict_types=1);

use Pin\Tests\Models\Models\User;

it('transforms queryable column', function () {
    $model = new User();

    expect($model->transformQueryableColumn('username'))->toBe('username')
        ->and($model->transformQueryableColumn('Username'))->toBe('username')
        ->and($model->transformQueryableColumn('userName'))->toBe('user_name');
});
