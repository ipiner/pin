<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Pin\Http\Middleware\ResponseHeaders;

it('adds response headers', function () {
    /** @var Response $response */
    $response = (new ResponseHeaders())->handle(
        app()->request,
        fn () => new Response()
    );

    expect(
        explode('.', $response->headers->get('x-request'))[0]
    )->toBe(app()->getRequestId());
});
