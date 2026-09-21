<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Pin\Exceptions\Exception;
use Pin\Support\Facades\Aes;
use Pin\Support\Json;
use Pin\Token\Exceptions\TokenInvalidException;
use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * AES Token 加解密
 */
trait AesHelper
{
    /**
     * 解密 Token
     *
     * @throws TokenInvalidException
     */
    protected function decrypt(string $encodedPayload): Token
    {
        try {
            $payload = Json::decode(Aes::decrypt($encodedPayload));
        } catch (Exception $exception) {
            throw new TokenInvalidException(new Token([], $encodedPayload), $exception);
        }

        if (! is_array($payload)) {
            throw new TokenInvalidException(new Token([], $encodedPayload));
        }

        return new Token($payload, $encodedPayload);
    }

    /**
     * 加密 Token
     */
    protected function encrypt(TokenPayload $payload): string
    {
        return Aes::encrypt(Json::encode($payload));
    }
}
