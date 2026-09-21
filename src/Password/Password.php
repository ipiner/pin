<?php

declare(strict_types=1);

namespace Pin\Password;

use Illuminate\Support\Facades\Hash;
use Pin\Crypt\CryptException;
use Pin\Errors\Errors;
use Pin\Support\Facades\Aes;

/**
 * 密码编码与校验
 */
class Password
{
    /**
     * 空密码编码
     */
    protected const string EMPTY_ENCODED_PASSWORD = '74BE16979710D4C4E7C6647856088456';

    /**
     * 校验密码是否正确
     *
     * @param  string  $encodedPassword  encode 后的密码（非明文）
     * @param  string  $salt  用户盐值
     * @param  string  $hashedPassword  数据库存储密码
     */
    public function check(string $encodedPassword, string $salt, string $hashedPassword): bool
    {
        return Hash::check($encodedPassword.$salt, $hashedPassword);
    }

    /**
     * 解密请求密码
     *
     * @throws PasswordException
     */
    public function decodeFromRequest(string $requestPassword): string
    {
        try {
            $encoded = Aes::decrypt($requestPassword);
        } catch (CryptException $e) {
            throw new PasswordException(
                '请求密码异常',
                Errors::PasswordDecodeFailed->code(),
                $e,
            );
        }

        if (! $this->isValid($encoded)) {
            throw new PasswordException(
                '请求密码异常',
                Errors::PasswordInvalid->code(),
            );
        }

        return $encoded;
    }

    /**
     * 编码明文密码
     *
     * @param  string  $plain  明文密码
     * @return string 32位大写字符串
     */
    public function encode(string $plain): string
    {
        return strtoupper(md5(md5($plain)));
    }

    /**
     * 生成请求传输密码
     *
     * @param  string  $plain  明文密码
     * @return string 加密后的请求密码
     */
    public function encodeToRequest(string $plain): string
    {
        return Aes::encrypt($this->encode($plain), true);
    }

    /**
     * 生成存储密码哈希
     *
     * @throws PasswordException
     */
    public function hash(string $encoded, string $salt): string
    {
        if (! $this->isValid($encoded)) {
            throw new PasswordException(
                '密码异常',
                Errors::PasswordInvalid->code(),
            );
        }

        return Hash::make($encoded.$salt);
    }

    /**
     * 是否为空密码编码
     */
    public function isEmpty(string $encoded): bool
    {
        return strtoupper($encoded) === static::EMPTY_ENCODED_PASSWORD;
    }

    /**
     * 是否符合密码编码格式
     */
    protected function isValid(string $encoded): bool
    {
        return strlen($encoded) === 32 && $encoded === strtoupper($encoded);
    }
}
