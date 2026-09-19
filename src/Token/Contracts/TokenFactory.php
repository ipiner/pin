<?php

declare(strict_types=1);

namespace Pin\Token\Contracts;

use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * Token 工厂接口。
 *
 * @method string encode(array|TokenPayload $payload, ?int $expires = null)
 * @method Token decode(string $token)
 */
interface TokenFactory
{
    /**
     * 获取驱动。
     */
    public function driver(): TokenDriver;
}
