<?php

declare(strict_types=1);

namespace Pin\Token;

/**
 * Token 实体对象
 *
 * @property ?int $uid 用户id
 * @property ?int $exp 过期时间
 * @property ?int $iat 签发时间
 * @property ?string $jti 唯一id
 * @property ?int $expires 有效期（秒）
 */
class Token
{
    /**
     * Token 载荷
     */
    public protected(set) TokenPayload $payload;

    /**
     * 原始 Token 字符串
     */
    public protected(set) string $raw;

    /**
     * Token 构造函数
     *
     * @param  array|TokenPayload  $payload  支持数组或已封装对象
     * @param  string  $raw  原始 token 字符串
     */
    public function __construct(array|TokenPayload $payload, string $raw)
    {
        // 统一转换为 TokenPayload 对象，避免外部结构不一致
        $this->payload = is_array($payload) ? new TokenPayload($payload) : $payload;
        $this->raw = $raw;
    }

    /**
     * 获取载荷属性
     */
    public function __get(string $key): mixed
    {
        return $this->payload[$key];
    }

    /**
     * 设置载荷属性
     */
    public function __set(string $key, mixed $value): void
    {
        $this->payload[$key] = $value;
    }

    /**
     * 判断载荷属性是否存在
     */
    public function __isset(string $key): bool
    {
        return isset($this->payload[$key]);
    }

    /**
     * 删除载荷属性
     */
    public function __unset(string $key): void
    {
        unset($this->payload[$key]);
    }
}
