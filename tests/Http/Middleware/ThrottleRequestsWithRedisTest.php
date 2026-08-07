<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pin\Http\Middleware\ThrottleRequestsWithRedis;
use Symfony\Component\HttpFoundation\Response;

it('encodes and decodes headers', function () {
    $headers = [60, 59, 120];

    $response = new Response();
    $response->headers->set(
        ThrottleRequestsWithRedis::HEADER_NAME,
        ThrottleRequestsWithRedis::encodeHeaders($headers),
    );

    expect(
        ThrottleRequestsWithRedis::decodeHeaders($response)
    )->toBe($headers);
});

it('returns empty array when header missing', function () {
    $response = new Response();

    expect(
        ThrottleRequestsWithRedis::decodeHeaders($response)
    )->toBe([]);
});

it('returns empty array when header invalid', function () {
    $response = new Response();
    $response->headers->set(
        ThrottleRequestsWithRedis::HEADER_NAME,
        'invalid',
    );

    expect(
        ThrottleRequestsWithRedis::decodeHeaders($response)
    )->toBe([]);
});

it('gets throttle headers', function () {
    $invoker = $this->invoker(app(ThrottleRequestsWithRedis::class));

    $response = new Response('', 200, [
        'X-RateLimit-Remaining' => 1,
    ]);

    expect(
        $invoker->getHeaders(3, 1, null, $response)
    )->toBe([]);

    expect(
        $invoker->getHeaders(3, 0, null, $response)
    )->toHaveKey(ThrottleRequestsWithRedis::HEADER_NAME);
});

describe('handles request', function () {
    it('returns encrypted throttle header', function () {
        config([
            'app.rate_limit.enabled' => true,
        ]);
        Route::middleware(ThrottleRequestsWithRedis::class.':20,1')
            ->get('/throttle-test', fn () => 'ok');
        $response = $this->get('/throttle-test');

        expect($response->headers->has(
            ThrottleRequestsWithRedis::HEADER_NAME
        ))->toBeTrue();
    });

    it('can be disabled', function () {
        config([
            'app.rate_limit.enabled' => false,
        ]);
        Route::middleware(ThrottleRequestsWithRedis::class.':20,1')
            ->get('/throttle-test', fn () => 'ok');
        $response = $this->get('/throttle-test');

        expect(
            $response->headers->has(
                ThrottleRequestsWithRedis::HEADER_NAME
            )
        )->toBeFalse();
    });
});
