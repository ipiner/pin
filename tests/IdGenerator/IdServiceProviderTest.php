<?php

declare(strict_types=1);

use Pin\IdGenerator\IdGeneratorServiceProvider;

it('declares provided services', function () {
    $provider = new IdGeneratorServiceProvider($this->app);
    $diff = array_diff(
        $provider->provides(),
        [
            'pin.id.timestamp',
            'pin.id.snowflake',
            'pin.id.redis',
        ]
    );
    expect($diff)->toBe([]);
});
