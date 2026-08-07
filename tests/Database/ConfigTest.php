<?php

declare(strict_types=1);

use Pin\Database\Config;

it('generates default MySQL config', function () {
    $config = Config::mysql('default');

    expect($config['host'])->toBeNull()
        ->and($config['port'])->toBe(3306);
});

it('overrides MySQL config with options', function () {
    $config = Config::mysql('default', [
        'host' => 'override-host',
        'port' => 3308,
    ]);

    expect($config['host'])->toBe('override-host')
        ->and($config['port'])->toBe(3308);
});
