<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Pin\Crypt\Middleware\Decrypt;
use Pin\Support\Facades\Aes;

beforeEach(function () {
    $this->middleware = $this->invoker(new Decrypt());
});

it('decrypts password', function () {
    $password = Str::random();
    $this->app['request']->query->set('password', Aes::encrypt($password));
    $this->middleware->handle($this->app['request'], fn () => true, 'password');

    expect(
        $this->app['request']->query('password'),
    )
        ->toBe($password);
});

it('decrypts plain value in non production', function () {
    $this->app['request']->query->set('password', 'plain:123456');
    $this->middleware->handle($this->app['request'], fn () => true, 'password');

    expect(
        $this->app['request']->query('password'),
    )
        ->toBe('123456');
});

it('accepts empty and zero plain values', function (string $value, string $expected) {
    $request = $this->app['request'];
    $request->query->set('password', $value);

    $this->middleware->handle($request, fn () => true, 'password');

    expect($request->query('password'))->toBe($expected);
})->with([
    ['plain:', ''],
    ['plain:0', '0'],
]);
