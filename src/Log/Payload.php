<?php

declare(strict_types=1);

namespace Pin\Log;

use Carbon\CarbonInterface;
use Pin\Support\DataBag;
use Pin\Support\Facades\Actor;

/**
 * 日志数据
 *
 * @property int $uid 用户 ID
 * @property string $username 用户名
 * @property string $user_type 用户类型
 * @property string $request_id 请求 ID
 * @property string $request_method HTTP 方法或 console
 * @property string $request_url 请求 URL 或命令行参数
 * @property string $route 路由名称或 URI
 * @property string|null $ip 客户端 IP
 * @property CarbonInterface $created_at 当前时间
 * @property array|null $context 扩展上下文
 */
class Payload extends DataBag
{
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->initialize();
    }

    /**
     * 合并扩展上下文
     */
    public function context(array $context): static
    {
        $this->context = array_merge($this->context ?? [], $context);

        return $this;
    }

    /**
     * 初始化基础数据
     */
    protected function initialize(): void
    {
        $this->created_at = now();
        $this->initUser()->initRequest();
    }

    /**
     * 初始化请求上下文
     */
    protected function initRequest(): static
    {
        $extra = ExtraProcessor::getExtra();

        $this->request_id = $extra['request_id'];
        $this->request_method ??= $extra['request_method'];
        $this->request_url ??= $extra['request_url'];
        $this->ip ??= $extra['ip'];
        $this->route ??= $extra['route'];

        return $this;
    }

    /**
     * 初始化用户上下文
     */
    protected function initUser(): static
    {
        $user = Actor::user();

        $this->uid ??= $user?->id ?? 0;
        $this->username ??= $user?->username ?? '';
        $this->user_type ??= Actor::type();

        return $this;
    }
}
