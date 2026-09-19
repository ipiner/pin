<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Pin\Auth\Guard;
use Pin\Errors\Errors;
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

it('preserves the original authentication exception', function () {
    $handler = new class($this->app) extends Handler
    {
        public ?Throwable $rendered = null;

        protected function prepareJsonResponse($request, Throwable $e): JsonResponse
        {
            $this->rendered = $e;

            return parent::prepareJsonResponse($request, $e);
        }
    };
    $request = $this->app['request'];
    $request->attributes->set(Guard::UNAUTHENTICATED_CODE, Errors::TokenExpired->code());
    $previous = new AuthenticationException();

    $response = $this->invoker($handler)->renderJsonException($request, $previous);

    expect($handler->rendered->getPrevious())->toBe($previous)
        ->and($response->getData(true)['code'])->toBe(Errors::AuthTokenExpired->code())
        ->and($response->getStatusCode())->toBe(401);
});

it('normalizes field messages when rendering Laravel validation exceptions', function () {
    $request = Request::create('/api/test');
    $exception = ValidationException::withMessages(['name' => ['1|Invalid name']]);

    $response = $this->handler->render($request, $exception);

    expect($response->getData(true)['data']['errors'])->toBe(['name' => ['Invalid name']])
        ->and($response->getData(true)['message'])->toBe('Invalid name')
        ->and($response->getStatusCode())->toBe(422);
});
