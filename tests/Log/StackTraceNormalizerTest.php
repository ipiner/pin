<?php

declare(strict_types=1);

use Pin\Log\StackTraceNormalizer;

beforeEach(function () {
    $this->normalizer = new StackTraceNormalizer();
    $this->invoker = $this->invoker($this->normalizer);
});

it('normalizes stack traces', function () {
    $e = new RuntimeException();
    config(['pin.logging.stack_trace.max_frames' => 1000]);

    $frames = $this->normalizer->normalize($e);
    expect(count($frames))
        ->toBe(count($e->getTrace()))
        ->and($frames[0])
        ->toStartWith('#0/');

    config(['pin.logging.stack_trace.max_frames' => 1]);
    expect(count($this->normalizer->normalize($e)))->toBe(1);

    config(['pin.logging.stack_trace.exclude_frames' => ['php']]);
    // #1/19 [internal]:? P\Tests\Log\StackTraceNormalizerTest->{closure:Pest\Factories\TestCaseMethodFactory::getClosure():167}
    expect(count($this->normalizer->normalize($e)))->toBe(1);
});

it('determines whether frames are excluded', function () {
    $frame = (new RuntimeException())->getTrace()[0];

    config(['pin.logging.stack_trace.exclude_frames' => ['.PHP']]);
    expect($this->invoker->isExcludedFrame($frame))->toBeFalse();

    config(['pin.logging.stack_trace.exclude_frames' => ['#\.PHP#i']]);
    expect($this->invoker->isExcludedFrame($frame))->toBeTrue();
});

it('determines whether frames are included', function () {
    $frame = (new RuntimeException())->getTrace()[0];

    expect($this->invoker->isIncludedFrame($frame))->toBeTrue();

    config(['pin.logging.stack_trace.include_frames' => ['.PHP']]);
    expect($this->invoker->isIncludedFrame($frame))->toBeFalse();

    config(['pin.logging.stack_trace.include_frames' => ['#\.PHP#i']]);
    expect($this->invoker->isIncludedFrame($frame))->toBeTrue();
});
