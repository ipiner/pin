<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobAttempted;
use Illuminate\Support\Facades\Log;
use Pin\Database\DatabaseServiceProvider;
use Pin\Database\QueryMonitor;
use Pin\Tests\Database\QueryMonitor\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

uses(TestCase::class);

it('registers the monitor before booting', function () {
    $container = new Container();
    (new DatabaseServiceProvider($container))->register();

    expect($container->make(QueryMonitor::class))->toBe($container->make(QueryMonitor::class));
});

it('flushes query logs after a queue attempt', function (bool $failed) {
    $monitor = app(QueryMonitor::class);
    $event = $this->getQueryExecuted(time: 0.25);
    $channel = Mockery::mock(LoggerInterface::class);
    Log::shouldReceive('channel')->once()->with('sql')->andReturn($channel);
    $channel->shouldReceive('log')->once()->with(
        LogLevel::DEBUG,
        'select username from users where uid = 1',
        [
            'category' => 'sql',
            'connection' => $event->connectionName,
            'time' => 0,
            'slow' => false,
        ]
    );

    $this->app['events']->dispatch($event);
    $this->app['events']->dispatch(new JobAttempted(
        'sync',
        Mockery::mock(Job::class),
        $failed ? new RuntimeException('Job failed.') : null
    ));

    expect($this->invoker($monitor->logger)->queries)->toBe([]);
})->with([false, true]);
