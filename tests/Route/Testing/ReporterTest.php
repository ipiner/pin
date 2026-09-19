<?php

declare(strict_types=1);

use App\Routes\User\UserRoute;
use Illuminate\Testing\TestResponse as BaseResponse;
use Pin\Route\RouteRegistrar;
use Pin\Route\Testing\Reporter;
use Pin\Route\Testing\TestResponse;
use Pin\Support\Invoker;

it('returns correct method color', function (string $method, string $expected): void {
    $invoker = new Invoker(new Reporter());
    expect($invoker->methodColor($method))->toBe($expected);
})->with([
    ['GET', 'green'],
    ['POST', 'yellow'],
    ['PUT', 'yellow'],
    ['DELETE', 'red'],
    ['PATCH', 'green'],
]);

it('reports request based on configuration', function () {
    RouteRegistrar::register(UserRoute::class);
    $stream = fopen('php://memory', 'r+');
    $reporter = new Reporter($stream);

    $resp = UserRoute::List->testing($this)->withReporter($reporter)->json();
    expect($reporter->reportRequest(UserRoute::List, '/', $resp))->toBeFalse();

    config(['testing.report_request_enabled' => true]);
    $resp = UserRoute::List->testing($this)->withReporter($reporter)->json();
    expect($reporter->reportRequest(UserRoute::List, '/', $resp))->toBeTrue();

    rewind($stream);
    $output = trim(stream_get_contents($stream));
    expect(str_starts_with($output, '[用户列表'))->toBeTrue($output);
});

it('correctly determines if reporting is enabled', function () {
    $reporter = new Reporter();
    $invoker = new Invoker($reporter);

    expect($invoker->reportRequestEnabled())->toBeFalse();

    config(['testing.report_request_enabled' => true]);
    expect($invoker->reportRequestEnabled())->toBeTrue();

    $reporter->reportRequestEnabled = false;
    expect($invoker->reportRequestEnabled())->toBeFalse();
});

it('returns correct status color', function (int $status, string $expected) {
    $invoker = new Invoker(new Reporter());
    expect($invoker->statusColor($status))->toBe($expected);
})->with([
    [200, 'green'],
    [204, 'green'],
    [301, 'yellow'],
    [400, 'yellow'],
    [500, 'red'],
    [501, 'red'],
]);

it('reports responses without requiring JSON', function (string $content, int $status) {
    $stream = fopen('php://memory', 'r+');

    try {
        $reporter = new Reporter($stream);
        $reporter->reportRequestEnabled = true;
        $response = new TestResponse(BaseResponse::fromBaseResponse(response($content, $status)));

        expect($reporter->reportRequest(UserRoute::Index, '/', $response))->toBeTrue();

        rewind($stream);
        expect(stream_get_contents($stream))->toContain((string) $status, $content);
    } finally {
        fclose($stream);
    }
})->with([
    'empty response' => ['', 204],
    'text response' => ['Service unavailable', 503],
    'JSON response' => ['{"code":0}', 200],
]);
