<?php

declare(strict_types=1);

use Pin\Exceptions\FakeResponseException;

it('creates fake json response exception', function () {
    $e = new FakeResponseException(['foo' => 'bar']);
    $resp = $e->toResponse($this->app['request']);

    expect($e->report)->toBeFalse()
        ->and($resp->getStatusCode())->toBe(200)
        ->and($resp->getContent())->toBe('{"foo":"bar"}');
});
