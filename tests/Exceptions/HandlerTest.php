<?php

declare(strict_types=1);

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Pin\Exceptions\Handler;
use Pin\Exceptions\ValidationException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

pest()->beforeEach(function () {
    $this->handler = new Handler($this->app);
    $this->invoker = $this->invoker($this->handler);
});

it('converts exceptions to array', function () {
    $data = $this->invoker->convertExceptionToArray(new RuntimeException());
    expect($data)->not()->toHaveKey('errors')
        ->and($data)->not()->toHaveKey('context');

    config(['app.debug' => true]);
    $e = new ValidationException(
        Illuminate\Validation\ValidationException::withMessages([])
    );
    $data = $this->invoker->convertExceptionToArray($e);
    expect($data)->toHaveKey('errors')
        ->and($data['context'])->not()->toBeNull();
});

it('renders exceptions', function () {
    expect(
        get_class(
            $this->handler->render(app()->request, new HttpResponseException(new SymfonyResponse()))
        )
    )->toBe(SymfonyResponse::class);

    // json response
    $request = Request::create('/');
    $request->headers->set('accept', 'application/json');
    $this->app->request = $request;

    expect($this->handler->render(app()->request, new Exception()))
        ->toBeInstanceOf(JsonResponse::class);
});

it('maps log levels', function () {
    expect($this->invoker->mapLogLevel(new RuntimeException()))
        ->toBe(LogLevel::ERROR)

        ->and($this->invoker->mapLogLevel(new ThrottleRequestsException()))
        ->toBe(LogLevel::INFO)

        ->and($this->invoker->mapLogLevel(new Exception()->withLogLevel(LogLevel::INFO)))
        ->toBe(LogLevel::INFO);
});

it("detects exceptions that shouldn't be reported", function () {
    expect($this->invoker->shouldntReport(new RuntimeException()))
        ->toBeFalse()

        ->and($this->invoker->shouldntReport(Errors::ValidationFailed->exception()))
        ->toBeTrue();
});
