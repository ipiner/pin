<?php

declare(strict_types=1);

use Pin\Database\QueryMonitor\QueryLogger;
use Pin\Tests\Database\QueryMonitor\TestCase;
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
