<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Exceptions\Exception;
use Pin\Exceptions\Handler;
use Pin\Http\ApiResponse;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    $this->handler = new Handler($this->app);
    $this->invoker = $this->invoker($this->handler);
});

describe('finalizes rendered response', function () {
    it('converts normal response to api response', function () {
        $request = Request::create('/api/users');
        $resp = $this->invoker->finalizeRenderedResponse(
            $request,
            new JsonResponse(),
            new Exception()
        );

        expect($resp->getData()->code)->toBe(500);
    });

    it('keeps api response unchanged', function () {
        $request = Request::create('/api/users');
        $resp = $this->invoker->finalizeRenderedResponse(
            $request,
            ApiResponse::make()->toResponse($request),
            new Exception()
        );

        expect($resp->getData()->code)->toBe(0);
    });
});

it('prepares the response', function () {
    $e = (new Exception())->withStatusCode(403);

    expect($this->invoker->render(app()->request, $e)->getStatusCode())->toBe(403);

    config(['app.debug' => true]);
    expect($this->invoker->prepareResponse(app()->request, $e)->getStatusCode())->toBe(500);
});

it('resolves status code', function () {
    $cases = [
        [500, new RuntimeException()],
        [500, new Exception()],
        [200, (new Exception())->withStatusCode(200)],
    ];

    foreach ($cases as $i => [$expected, $exception]) {
        expect($this->invoker->resolveStatusCode($exception))->toBe($expected, "Case #$i failed");
    }
});

it('resolves response codes', function (int $expected, Throwable $exception) {
    expect($this->invoker->resolveResponseCode($exception))
        ->toBe($expected);
})->with([
    'custom exception code' => [
        422,
        new Exception(code: 422),
    ],

    'fallback server error' => [
        500,
        new Exception(code: 0),
    ],

    'not found http exception' => [
        404,
        new NotFoundHttpException(),
    ],

    'runtime exception' => [
        500,
        new RuntimeException(),
    ],
]);

it('resolves response messages', function (string|IError $expected, Throwable $exception) {
    expect($this->invoker->resolveResponseMessage($exception))
        ->toBe(is_string($expected) ? $expected : $expected->message());
})->with([
    'bad request' => [
        Errors::BadRequest,
        new Exception(previous: new SuspiciousOperationException()),
    ],

    'not found http message' => [
        Response::$statusTexts[404],
        new NotFoundHttpException(),
    ],

    'http exception custom message' => [
        'http error',
        new HttpException(1000, 'http error'),
    ],

    'custom error message' => [
        'error message',
        (new Exception())->withResponseMessage('error message'),
    ],

    'server error fallback' => [
        Errors::ServerError,
        new Exception('error message'),
    ],

    'custom status keeps message' => [
        'error message',
        (new Exception('error message'))->withStatusCode(200),
    ],

    'runtime exception hidden message' => [
        Errors::ServerError,
        new RuntimeException(),
    ],
]);

it('shows runtime exception messages in debug mode', function () {
    config(['app.debug' => true]);

    expect(
        $this->invoker->resolveResponseMessage(
            new RuntimeException('error message')
        )
    )->toBe('error message')
        ->and(
            $this->invoker->resolveResponseMessage(
                new RuntimeException('')
            )
        )->toBe(Errors::ServerError->message());
});
