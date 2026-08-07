<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Pin\Exceptions\Exception;
use Pin\Exceptions\FakeResponseException;
use Pin\Exceptions\Handler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    $this->handler = new Handler($this->app);
    $this->invoker = $this->invoker($this->handler);
});

it('prepares json response with correct status codes', function () {
    $cases = [
        [404, new NotFoundHttpException()],
        [500, new RuntimeException()],
    ];

    foreach ($cases as $case) {
        expect(
            $this->invoker->prepareJsonResponse($this->app->request, $case[1])->getStatusCode()
        )->toBe($case[0]);
    }
});

it('renders json exceptions', function () {
    $resp = $this->invoker->renderJsonException(
        app()->request, new FakeResponseException(['name' => 'foo'])
    );
    expect($resp)->toBeInstanceOf(JsonResponse::class)
        ->and($resp->getData(true))->toBe(['name' => 'foo']);

    $e = ValidationException::withMessages(['username' => 'invalid username']);
    $resp = $this->invoker->renderJsonException(app()->request, $e);

    expect($resp)->toBeInstanceOf(JsonResponse::class)
        ->and($resp->getData()->data->errors->username)->not()->toBeNull();

    $resp = $this->invoker->renderJsonException(app()->request, new Exception());
    expect($resp->getData(true))->not()->toHaveKey('errors');

    $resp = $this->invoker->renderJsonException(app()->request, new AuthenticationException());
    expect($resp->getData()->code)->toBe(401);
});

it('determines if json should be returned', function () {
    $request = Request::create('/api/users');
    expect($this->invoker->shouldReturnJson($request, new Exception()))->toBeTrue();
});
