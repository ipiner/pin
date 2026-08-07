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
