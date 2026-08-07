<?php

declare(strict_types=1);

use Pin\Log\SkipTrace;
use Pin\Log\StackTracePolicy;

beforeEach(function () {
    $this->policy = new StackTracePolicy();
    $this->invoker = $this->invoker($this->policy);
});

it('determines whether stack traces should be included', function () {
    // default disabled
    expect($this->policy->shouldInclude(new RuntimeException()))->toBeFalse();

    // enabled
    config(['pin.logging.stack_trace.enabled' => true]);

    expect($this->policy->shouldInclude(new RuntimeException()))->toBeTrue();
});

it('determines whether exceptions are excluded', function () {
    expect(
        $this->invoker->isExcludedException(
            new class extends RuntimeException implements SkipTrace
            {
            }
        )
    )->toBeTrue();

    expect($this->invoker->isExcludedException(new RuntimeException()))->toBeFalse();

    config([
        'pin.logging.stack_trace.exclude_exceptions' => [RuntimeException::class],
    ]);
    expect($this->invoker->isExcludedException(new RuntimeException()))->toBeTrue();
});

it('determines whether exceptions are included', function () {
    expect($this->invoker->isIncludedException(new RuntimeException()))->toBeTrue()
        ->and($this->invoker->isIncludedException(new LogicException()))->toBeTrue();

    config([
        'pin.logging.stack_trace.include_exceptions' => [LogicException::class],
    ]);

    expect($this->invoker->isIncludedException(new RuntimeException()))->toBeFalse()
        ->and($this->invoker->isIncludedException(new LogicException()))->toBeTrue();
});
