<?php

declare(strict_types=1);

namespace Pin\Password;

use Illuminate\Support\Facades\Hash;
use Pin\Crypt\CryptException;
use Pin\Support\Facades\Aes;

/**
 * 密码加解密
 */
class Password
{
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
     * 将请求中的密码解密为 encode 后的密码
     *
     * @throws PasswordException
     */
    public function decodeFromRequest(string $requestPassword): string
    {
        // 前端 AES 加密 → 后端解密
        try {
            $encoded = Aes::decrypt($requestPassword);
        } catch (CryptException $e) {
            throw new PasswordException(
                "请求密码异常[{$requestPassword}]",
                $e->getCode(),
                $e,
            );
        }

        // 校验是否符合 encode 后的格式（32位大写）
        if ($this->isValid($encoded)) {
            return $encoded;
        }

        throw new PasswordException(
            "请求密码异常[{$requestPassword}: {$encoded}]",
        );
    }

    /**
     * 对明文密码进行编码（用于 hash 前）
     *
     * @param  string  $plain  明文密码
     * @return string 32位大写字符串
     */
    public function encode(string $plain): string
    {
        return strtoupper(md5(strtoupper($plain)));
    }

    /**
     * 将明文密码转为请求传输格式
     *
     * @param  string  $rawPassword  明文密码
     * @return string 加密后的请求密码
     */
    public function encodeToRequest(string $rawPassword): string
    {
        return Aes::encrypt(static::encode($rawPassword), true);
    }

    /**
     * 生成密码哈希（存储用）
     *
     * @throws PasswordException
     */
    public function hash(string $password, string $salt): string
    {
        // 必须为 encode 后格式（32位大写）
        if ($this->isValid($password)) {
            return Hash::make($password.$salt);
        }

        throw new PasswordException("密码异常[{$password}]");
    }

    /**
     * 校验密码是否合法（encode 后格式）
     */
    protected function isValid(string $password): bool
    {
        return strlen($password) === 32 && $password === strtoupper($password);
    }
}
