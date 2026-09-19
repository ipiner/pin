<?php

declare(strict_types=1);

use Pin\Exceptions\Exception;
use Pin\Support\Json;

it('decodes json', function () {
    expect(Json::decode('"中国"'))->toBe('中国')
        ->and(Json::decode(json_encode([])))->toBeArray();

    Json::decode('中'[0]);
})->throws(Exception::class);

it('encodes json', function () {
    expect(Json::encode('中国'))->toContain('中国')
        ->and(json_encode('中国'))->not()->toContain('中国');

    Json::encode('中'[0]);
})->throws(Exception::class);

it('wraps encoding errors when custom options are supplied', function () {
    try {
        Json::encode("\xFF", JSON_UNESCAPED_UNICODE);
        $this->fail('Expected an encoding exception.');
    } catch (Exception $exception) {
        expect($exception->getPrevious())->toBeInstanceOf(JsonException::class)
            ->and($exception->getCode())->toBe(JSON_ERROR_UTF8);
    }
});

it('preserves custom JSON encoding options', function () {
    expect(Json::encode('中国', 0))->toBe('"\\u4e2d\\u56fd"')
        ->and(Json::encode("\xFF", JSON_INVALID_UTF8_SUBSTITUTE))->toBe('"\\ufffd"');
});
