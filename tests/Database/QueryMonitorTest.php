<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
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

it('formats SQL only when collected', function (
    bool $logging,
    bool $response,
    bool $ignored,
    float $time,
    bool $logged
) {
    config([
        'app.debug' => false,
        'pin.logging.sql_logging' => $logging,
        'pin.logging.response.include_sql' => $response,
        'logging.channels.sql.ignores' => $ignored ? ['users'] : [],
    ]);

    $event = Mockery::mock(QueryExecuted::class, [
        'select * from users where uid = ?', [1], $time, $this->getConnection(),
    ])->makePartial();
    $event->shouldReceive('toRawSql')
        ->times($logged || $response ? 1 : 0)
        ->andReturn('select * from users where uid = 1');

    $monitor = app(QueryMonitor::class);
    $monitor->handle($event);

    expect($monitor->profile->count)->toBe(1)
        ->and($monitor->profile->time)->toBe((int) $time)
        ->and($monitor->response->all())->toHaveCount($response ? 1 : 0)
        ->and($this->invoker($monitor->logger)->queries)->toHaveCount($logged ? 1 : 0);
})->with([
    'disabled' => [false, false, false, 0.25, false],
    'log only' => [true, false, false, 0.25, true],
    'response only' => [false, true, false, 0.25, false],
    'both enabled' => [true, true, false, 0.25, true],
    'ignored log' => [true, false, true, 0.25, false],
    'ignored log with response' => [true, true, true, 0.25, false],
    'slow query' => [false, false, false, 2000.0, true],
    'ignored slow query' => [false, false, true, 2000.0, false],
]);

it('starts with empty statistics in a new scope', function () {
    $monitor = app(QueryMonitor::class);
    $monitor->handle($this->getQueryExecuted());

    $this->app->forgetScopedInstances();
    $next = app(QueryMonitor::class);

    expect($next)->not->toBe($monitor)
        ->and($next->profile->count)->toBe(0)
        ->and($next->profile->time)->toBe(0)
        ->and($next->response->all())->toBe([])
        ->and($this->invoker($next->logger)->queries)->toBe([]);
});
