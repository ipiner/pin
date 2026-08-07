<?php

declare(strict_types=1);

use Pin\Token\Token;

it('creates and manipulates token properties', function () {
    $token = new Token(['uid' => 1], '1');

    expect(isset($token->uid))->toBeTrue();
    expect($token->uid)->toBe(1);

    $token->uid = 2;
    expect($token->uid)->toBe(2);

    unset($token->uid);
    expect(isset($token->uid))->toBeFalse();

    // Accessing unset property in strict mode should throw an exception
    $this->expectExceptionMessage('Undefined array key "uid');
    $token->uid;
});
