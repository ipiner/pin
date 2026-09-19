<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Pin\Faker\Fake;

it('respects bounds when inferring values', function (string $rules) {
    $data = Fake::generate(['value' => $rules]);

    expect(Validator::make($data, ['value' => $rules])->passes())->toBeTrue();
})->with([
    'string minimum' => 'required|string|min:32',
    'string range' => 'required|string|min:8|max:12',
    'integer minimum' => 'required|integer|min:20000',
    'integer maximum' => 'required|integer|max:-10',
    'zero maximum' => 'required|integer|max:0',
    'integer range' => 'required|integer|min:-10|max:10',
]);

it('prefers email inference over string inference', function () {
    $rules = ['email' => 'required|string|email'];

    expect(Validator::make(Fake::generate($rules), $rules)->passes())->toBeTrue();
});
