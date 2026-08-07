<?php

declare(strict_types=1);

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
            return self::resolvePlainInput($value) ?? $value;
        }
    };
});

it('resolves plain input', function ($env, $input, $expected) {
    app()->detectEnvironment(fn () => $env);

    expect(
        TransformsRequest::resolvePlainInput($input)
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
]);
