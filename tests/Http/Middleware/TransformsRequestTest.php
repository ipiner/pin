<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Pin\Http\Middleware\TransformsRequest;

beforeEach(function () {
    $this->middleware = new class extends TransformsRequest
    {
        protected array $fields = ['password'];

        public function transform($key, $value)
        {
            return parent::transform($key, $value);
        }

        protected function normalize(string $value): string
        {
            return self::resolvePlainValue($value) ?? $value;
        }
    };
});

it('resolves plain input', function ($env, $input, $expected) {
    app()->detectEnvironment(fn () => $env);

    expect(
        TransformsRequest::resolvePlainValue($input)
    )->toBe($expected);
})->with([
    ['production', 'plain:123', null],
    ['testing', 'plain:123', '123'],
]);

it('transforms value', function ($field, $value, $expected) {
    expect(
        $this->middleware->transform($field, $value)
    )->toBe($expected);
})->with([
    ['password', 'plain:123', '123'],
    ['password', '123', '123'],
    ['password', null, ''],
    ['username', 'foo', 'foo'],
    ['age', 18, 18],
    ['enabled', false, false],
    ['remark', null, null],
]);

it('preserves unrelated JSON fields while transforming passwords', function () {
    $data = [
        'password' => 'plain:123',
        'age' => 18,
        'enabled' => false,
        'profile' => ['score' => 1.5, 'remark' => null],
    ];
    $request = Request::create(
        '/',
        'POST',
        server: ['CONTENT_TYPE' => 'application/json'],
        content: json_encode($data)
    );

    $result = $this->middleware->handle($request, fn ($request) => $request->json()->all());
    $data['password'] = '123';

    expect($result)->toBe($data);
});
