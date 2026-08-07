<?php

declare(strict_types=1);

use Pin\Log\Config;

it('builds daily log config', function () {
    $config = Config::daily('app');

    expect($config['path'])->toBe(storage_path('testing-logs/app.log'))
        ->and($config['driver'])->toBe('daily')
        ->and($config['days'])->toBe(14);
});

it('builds single log config', function () {
    $single = Config::single('app');
    $singleError = Config::single('app', ['level' => 'error']);

    expect($single['path'])->toBe(storage_path('testing-logs/app.log'))
        ->and($single['level'])->toBe('debug')
        ->and($singleError['level'])->toBe('error');
});

it('resolves log paths', function () {
    $invoker = $this->invoker(Config::class);

    expect($invoker->resolveLogPath('app'))->toBe(storage_path('testing-logs/app.log'))
        ->and($invoker->resolveLogPath('app', 'production'))->toBe(storage_path('logs/app.log'));
});
