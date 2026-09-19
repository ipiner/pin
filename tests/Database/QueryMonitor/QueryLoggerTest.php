<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Pin\Database\QueryMonitor\QueryLogger;
use Pin\Tests\Database\QueryMonitor\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

uses(TestCase::class);

it('logs and flushes queries', function () {
    $logger = app(QueryLogger::class);

    $o = $this->invoker($logger);
    $event = $this->getQueryExecuted();

    $logger->push($event, $event->sql);

    // ignore
    config(['logging.channels.sql.ignores' => ['users']]);
    $logger->push($event, $event->sql);
    expect($o->queries)->toHaveCount(1);

    // flush
    $logger->flush();

    expect($o->queries)->toHaveCount(0);
});

it('determines whether queries should be ignored', function () {
    $logger = app(QueryLogger::class);

    $o = $this->invoker($logger);
    $event = $this->getQueryExecuted();

    expect($o->isIgnored($event))->toBeFalse();

    config(['logging.channels.sql.ignores' => ['users', 'orders']]);
    expect($o->isIgnored($event))->toBeTrue();

    config(['logging.channels.sql.ignores' => ['/user*/']]);
    expect($o->isIgnored($event))->toBeTrue();

    config(['logging.channels.sql.ignores' => ['Users']]);
    expect($o->isIgnored($event))->toBeFalse();
});

it('resolves correct log level', function () {
    $logger = app(QueryLogger::class);
    $o = $this->invoker($logger);

    expect($o->resolveLogLevel(['slow' => true]))->toBe(LogLevel::NOTICE)
        ->and($o->resolveLogLevel(['slow' => false]))->toBe(LogLevel::DEBUG);
});

it('flushes a batch through one log channel', function () {
    $logger = app(QueryLogger::class);
    $channel = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->once()->with('sql')->andReturn($channel);

    foreach ([0.25, 2000.0] as $time) {
        $event = $this->getQueryExecuted(time: $time);
        $logger->push($event, $event->sql);
        $channel->shouldReceive('log')->once()->with(
            $time >= 2000 ? LogLevel::NOTICE : LogLevel::DEBUG,
            $event->sql,
            [
                'category' => 'sql',
                'connection' => $event->connectionName,
                'time' => (int) $time,
                'slow' => $time >= 2000,
            ]
        );
    }

    $logger->flush();
    $logger->flush();
});

it('preserves queries collected while flushing', function () {
    $logger = app(QueryLogger::class);
    $event = $this->getQueryExecuted();
    $channel = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->once()->with('sql')->andReturn($channel);
    $channel->shouldReceive('log')->once()->andReturnUsing(function () use ($logger, $event) {
        $logger->push($event, 'select 2');
    });

    $logger->push($event, 'select 1');
    $logger->flush();

    expect(array_column($this->invoker($logger)->queries, 'sql'))->toBe(['select 2']);
});
