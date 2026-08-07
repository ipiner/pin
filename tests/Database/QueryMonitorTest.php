<?php

declare(strict_types=1);

use Pin\Database\QueryMonitor;
use Pin\Tests\Database\QueryMonitor\TestCase;

uses(TestCase::class);

it('monitors queries', function () {
    $monitor = app(QueryMonitor::class);

    // 单例
    expect($monitor)->toBe(app(QueryMonitor::class));

    $event = $this->getQueryExecuted();
    $monitor->handle($event);

    // 忽略配置表
    config(['logging.channels.sql.ignores' => ['users']]);
    $monitor->handle($event);

    expect($monitor->profile->count)->toBe(2)
        ->and(count($this->invoker($monitor->logger)->queries))->toBe(1);
});
