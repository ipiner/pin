<?php

declare(strict_types=1);

use Pin\Database\QueryMonitor\QueryResponse;
use Pin\Tests\Database\QueryMonitor\TestCase;

uses(TestCase::class);

it('logs query response data', function () {
    $resp = new QueryResponse();
    $event = $this->getQueryExecuted();

    $resp->push($event, $event->sql);
    expect($resp->all())->toHaveCount(1);

    // ignore
    config(['pin.logging.response.include_sql' => false]);
    config(['app.debug' => false]);
    $resp->push($event, $event->sql);

    expect($resp->all())->toHaveCount(1);
});

it('returns query durations as integer milliseconds', function () {
    $response = new QueryResponse();
    $event = $this->getQueryExecuted(time: 0.25);

    $response->push($event, $event->sql);

    expect($response->all())->toBe([['sql' => $event->sql, 'time' => 0]]);
});
