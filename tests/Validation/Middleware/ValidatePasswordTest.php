<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Pin\Errors\Errors;
use Pin\Validation\Middleware\ValidatePassword;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->middleware = new ValidatePassword();
});

it('skips empty passwords', function (array $payload) {
    $request = Request::create('/', 'POST', $payload);
    $response = $this->middleware->handle(
        $request,
        fn () => new Response(status: 204)
    );

    expect($response->getStatusCode())->toBe(204);
})->with([
    'missing' => [[]],
    'null' => [['password' => null]],
    'empty string' => [['password' => '']],
]);

it('validates filled password fields', function () {
    $request = Request::create('/', 'POST', ['password' => 'test@123']);
    $response = $this->middleware->handle(
        $request,
        fn () => new Response(status: 204)
    );

    expect($response->getStatusCode())->toBe(204);
});

it('rejects invalid filled password fields', function (mixed $password, int $code) {
    $request = Request::create('/', 'POST', ['password' => $password]);

    try {
        $this->middleware->handle($request, fn () => new Response(status: 204));
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('password')
            ->and($e->errors()['password'][0])->toStartWith($code.'|');

        return;
    }

    $this->fail('Expected password validation to fail');
})->with([
    'too short' => ['123456', Errors::PasswordTooShort->code()],
    'insufficient types' => ['testtest', Errors::PasswordInsufficientTypes->code()],
]);

it('rejects non string password fields', function () {
    $request = Request::create('/', 'POST', ['password' => ['test@123']]);

    $this->middleware->handle($request, fn () => new Response(status: 204));
})->throws(ValidationException::class);

it('validates custom fields', function () {
    $request = Request::create('/', 'POST', [
        'password' => '',
        'new_password' => 'testtest',
    ]);

    $this->middleware->handle(
        $request,
        fn () => new Response(status: 204),
        'password',
        'new_password',
    );
})->throws(ValidationException::class);
