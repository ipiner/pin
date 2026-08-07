<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Routing\Route;
use Monolog\Level;
use Monolog\LogRecord;
use Pin\Log\ExtraProcessor;

it('provides extra context for HTTP requests', function () {
    $context = $this->invoker(ExtraProcessor::class)->extraForHttp();

    expect($context['request_method'])->toBe('GET')
        ->and(str_contains($context['request_url'], 'vendor'))->toBeFalse();
});

it('provides extra context for console requests', function () {
    $extras = ExtraProcessor::getExtra();

    expect($extras['request_method'])->toBe('console')
        ->and(str_contains($extras['request_url'], 'vendor'))->toBeTrue();
});

it('resolves route names', function () {
    expect(ExtraProcessor::getRoute())->toBe('');

    $route = new Route('GET', '/users', fn () => true);
    expect(ExtraProcessor::getRoute($route))->toBe('users');

    $route->name('generated::');
    expect(ExtraProcessor::getRoute($route))->toBe('users');

    $route = new Route('GET', '/users', fn () => true);
    $route->name('user.list');
    expect(ExtraProcessor::getRoute($route))->toBe('user.list');
});

it('invokes the processor and returns extra fields', function () {
    $record = new LogRecord(new DateTimeImmutable(), 'channel', Level::Info, '');
    $result = (new ExtraProcessor())($record);

    expect($result['extra'])->toHaveKey('uid');
});

describe('resolves uid', function () {
    it('returns auth id', function () {
        $this->actingAs(new User(['id' => 123]));
        $uid = $this->invoker(ExtraProcessor::class)->getUid();

        expect($uid)->toBe(123);
    });

    it('returns null when auth throws exception', function () {
        Auth::shouldReceive('id')
            ->once()
            ->andThrow(new RuntimeException('fail'));
        $uid = $this->invoker(ExtraProcessor::class)->getUid();

        expect($uid)->toBeNull();
    });
});
