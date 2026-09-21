<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Http\Request;
use Pin\Token\Token;

/**
 * 请求 Token 解析器
 */
class TokenResolver
{
    /**
     * 已解析的 Token
     */
    protected ?Token $resolvedToken = null;

    public function __construct(
        protected ?Request $request = null,
        protected ?string $tokenKey = null,
    ) {
    }

    /**
     * 注销当前已解析的 Token
     */
    public function forgetToken(): void
    {
        $token = $this->resolvedToken;
        $this->resolvedToken = null;

        if ($token) {
            Auth::token()->forget($token);
        }
    }

    public function getRequest(): Request
    {
        return $this->request ?? app('request');
    }

    /**
     * 从当前请求中获取 Token
     */
    public function getRequestToken(): ?string
    {
        $request = $this->getRequest();
        $token = $this->normalizeToken($request->bearerToken());

        if ($token) {
            return $token;
        }

        $tokenKey = $this->getTokenKey();
        $token = $this->normalizeToken($request->header($tokenKey));

        if ($token) {
            return $token;
        }

        return $this->normalizeToken($request->query->all()[$tokenKey] ?? null);
    }

    /**
     * 获取已解析的 Token
     */
    public function getResolvedToken(): ?Token
    {
        return $this->resolvedToken;
    }

    public function getTokenKey(): string
    {
        return $this->tokenKey ?? config('auth.guards.pin.token_key', 'token');
    }

    /**
     * 获取用户 ID
     */
    public function getUid(): ?int
    {
        return $this->resolvedToken?->uid;
    }

    /**
     * 判断是否为 Sanctum Token
     */
    public function isSanctumToken(string $token): bool
    {
        return str_contains($token, '|') && (int) $token > 0;
    }

    /**
     * 解析请求 Token
     */
    public function resolve(?string $requestToken): ?Token
    {
        // 清理上次解析结果
        $this->resolvedToken = null;
        $requestToken = $this->normalizeToken($requestToken);

        if (! $requestToken || $this->isSanctumToken($requestToken)) {
            return null;
        }

        return $this->resolvedToken = Auth::token()->decode($requestToken);
    }

    /**
     * 设置当前请求
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;
        $this->resolvedToken = null;

        return $this;
    }

    protected function normalizeToken(mixed $token): ?string
    {
        if (! is_string($token)) {
            return null;
        }

        $token = trim($token);

        return $token === '' ? null : $token;
    }
}
