<?php

use Pin\Support\Duration;
use Pin\Support\Timer;

it('calculates duration since request start', function () {
    expect(
        (new Timer())->durationSinceStartOfRequest(time() - 10)->seconds() > 1
    )->toBeTrue();
});

it('starts timers', function () {
    $timer = new Timer();

    $timer->start();
    $timer->start('name');

    $timers = $this->invoker($timer)->timers;

    expect($timers)->toHaveKey('default')
        ->and($timers)->toHaveKey('name');

    $timer->start();
})->throws(LogicException::class);

it('stops timers', function () {
    $timer = new Timer();

    $timer->start();

    expect($timer->stop())->toBeInstanceOf(Duration::class)
        ->and($this->invoker($timer)->timers)->not->toHaveKey('default');

    $timer->stop();
})->throws(LogicException::class);
