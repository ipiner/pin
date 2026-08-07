<?php

use Illuminate\Support\Facades\Request;

beforeEach(function () {
    $this->request = app()->request;
});

it('detects requests from API document', function () {
    expect($this->request->isFromApiDocument())->toBeFalse();

    $request = Request::create('/');
    config(['app.x_api_document.enabled' => false]);
    $request->headers->set('x-api-document', 'Apifox');
    expect($request->isFromApiDocument())->toBeFalse();

    config(['app.x_api_document.enabled' => true]);
    $request->headers->set('x-api-document', 'Apifox');
    expect($request->isFromApiDocument())->toBeTrue();

    $request->headers->set('x-api-document', '');
    $request->headers->set('referer', 'http://localhost/docs/api/');
    expect($request->isFromApiDocument())->toBeTrue();
});

it('detects reading requests', function () {
    expect($this->request->isReading())->toBeTrue();

    foreach (['HEAD', 'GET', 'OPTIONS'] as $method) {
        $request = Request::create('/', $method);
        expect($request->isReading())->toBeTrue();
    }

    foreach (['POST', 'PUT', 'DELETE'] as $method) {
        $request = Request::create('/', $method);
        expect($request->isReading())->toBeFalse();
    }
});

it('checks if request matches URI', function () {
    $request = Request::create('/api/captcha');

    expect($request->isRequest('api/captcha'))->toBeTrue()
        ->and($request->isRequest('/api/captcha'))->toBeTrue()
        ->and($request->isRequest('captcha'))->toBeFalse();
});

describe('gets referer', function () {
    it('returns x-referer header first', function () {
        $request = Request::create('/');

        $request->headers->set('x-referer', 'https%3A%2F%2Fexample.com%2Fpage');
        $request->headers->set('referer', 'https://other.com');

        expect($request->getReferer())->toBe('https://example.com/page');
    });

    it('returns referer header when x-referer is missing', function () {
        $request = Request::create('/');

        $request->headers->set('referer', 'https%3A%2F%2Fexample.com');

        expect($request->getReferer())->toBe('https://example.com');
    });

    it('returns empty string when headers are missing', function () {
        expect(Request::create('/')->getReferer())->toBe('');
    });
});
