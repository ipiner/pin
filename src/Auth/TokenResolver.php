<?php

declare(strict_types=1);

namespace Pin\Auth;

use Pin\Token\Token;

/**
 * Token 解析器。
 *
 * 负责从请求中提取认证 Token，并将其解析为认证系统可识别的
 * Token 实例。解析结果会在当前请求生命周期内缓存。
 */
class TokenResolver
{
    /**
     * 当前请求已解析的 Token 实例。
     */
    protected ?Token $resolvedToken = null;

    /**
     * 注销当前已解析的 Token。
     */
    public function forgetToken(): void
    {
        Auth::token()->forget($this->resolvedToken);
        $this->resolvedToken = null;
    }

    /**
     * 从当前请求中获取 Token。
     *
     * 读取优先级为：Authorization Bearer、指定 Header、Query 参数。
     */
    public function getRequestToken(): ?string
    {
        // 标准 Bearer Token
        if ($token = app()->request->bearerToken()) {
            return $token;
        }

        // 自定义 Header
        $tokenKey = config('auth.guards.pin.token_key', 'token');
        if ($token = app()->request->header($tokenKey)) {
            return $token;
        }

        // Query 参数
        return app()->request->query($tokenKey);
    }

    /**
     * 获取当前请求已解析的 Token 实例。
     */
    public function getResolvedToken(): ?Token
    {
        return $this->resolvedToken ?? null;
    }

    /**
     * 获取当前 Token 关联的用户 ID。
     */
    public function getUid(): ?int
    {
        return $this->resolvedToken?->uid;
    }

    /**
     * 判断给定 Token 是否为 Laravel Sanctum Token 格式。
     */
    public function isSanctumToken(string $token): bool
    {
        return str_contains($token, '|') && (int) $token > 0;
    }

    /**
     * 解析原始请求 Token。
     *
     * 空 Token 和 Sanctum Token 会被忽略。
     *
     * @param  string|null  $requestToken  原始 Token
     */
    public function resolve(?string $requestToken): ?Token
    {
        // 无 Token 或 Sanctum Token，直接忽略
        if (! $requestToken || $this->isSanctumToken($requestToken)) {
            return null;
        }

        return $this->resolvedToken = Auth::token()->decode($requestToken);
    }
}
