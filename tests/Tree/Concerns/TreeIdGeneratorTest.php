<?php

declare(strict_types=1);

use Pin\Tree\Concerns\TreeIdGenerator;

it('generates a default node id', function () {
    $model = new class
    {
        use TreeIdGenerator;
    };

    $id = $model->generateNodeId();
    expect($id)->toBeInt()->and($id)->toBeGreaterThan(0);
});

it('uses a custom generator if defined', function () {
    $model = new class
    {
        use TreeIdGenerator;

        public function newUniqueId(): int
        {
            return 123456;
        }
    };

    expect($model->generateNodeId())->toBe(123456);
});
