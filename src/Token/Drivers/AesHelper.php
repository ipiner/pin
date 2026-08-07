<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Pin\Crypt\CryptException;
use Pin\Support\Facades\Aes;
use Pin\Support\Json;
use Pin\Token\Exceptions\TokenInvalidException;
use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * AES Token 加解密辅助方法。
 *
 * 主要用于对称加密类型的 Token Driver。
 */
trait AesHelper
{
    /**
     * 解密 Token 字符串并恢复为 Token 对象。
     *
     * 解密失败时会抛出 TokenInvalidException，
     * 表示当前 Token 非法、损坏或已被篡改。
     *
     * @param  string  $encodedPayload  已加密的 Token 字符串
     *
     * @throws TokenInvalidException
     */
    protected function decrypt(string $encodedPayload): Token
    {
        try {
            $payload = Json::decode(Aes::decrypt($encodedPayload));

            return new Token($payload, $encodedPayload);
        } catch (CryptException) {
            throw new TokenInvalidException(
                new Token([], $encodedPayload)
            );
        }
    }

    /**
     * 加密 TokenPayload 为 Token 字符串。
     *
     * 流程：
     * 1. Payload JSON 序列化
     * 2. 使用 AES 对称加密
     *
     * @param  TokenPayload  $payload  Token Payload
     */
    protected function encrypt(TokenPayload $payload): string
    {
        return Aes::encrypt(Json::encode($payload));
    }
}
