<?php

declare(strict_types=1);

namespace Pin\Route;

use Pin\Attributes\Attribute;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Prefix;

/**
 * RouteDefinition
 *
 * 将路由定义字符串解析为 HTTP 方法、URI 和路由名称
 */
class RouteDefinition
{
    /**
     * 路由名称
     */
    public string $name;

    /**
     * HTTP 请求方法
     */
    public string $method;

    /**
     * 路由 URI（去掉前后斜杠）
     */
    public string $uri;

    /**
     * 构造函数
     */
    public function __construct(protected Routable $route)
    {
        $this->resolve();
    }

    /**
     * 解析路由定义
     */
    protected function resolve(): void
    {
        $verb = $this->route->value;
        $name = '';

        // case Login = 'POST:/auth/login|auth.login
        if (str_contains($this->route->value, '|')) {
            [$verb, $name] = explode('|', $this->route->value, 2);
        }

        // "GET:/api/users" -> ["GET", "/api/users"]
        [$this->method, $this->uri] = explode(':', trim($verb), 2);
        $this->method = strtoupper($this->method);
        $this->uri = $this->resolveUri();

        $this->name = trim($name);
        if ($this->name === '') {
            $this->name = $this->resolveName();
        }
    }

    /**
     * 自动生成路由名称
     *
     * - GET:/api/users -> users
     * - GET:/api/users/{id} -> users.detail
     * - POST:/api/users -> users.create
     * - PUT:/api/users/{id} -> users.update
     * - DELETE:/api/users/{id} -> users.delete
     */
    protected function resolveName(): string
    {
        // #[Name('name')]
        $attr = $this->route->attribute(Name::class);
        if ($attr) {
            return $attr->value;
        }

        $name = str_replace('/api/', '', '/'.trim($this->uri, '/')); // /api/users -> users
        $name = str_replace('/', '.', $name);

        $suffix = $this->resolveNameSuffix();
        if (str_contains($name, '{id}')) {
            // {id} => detail
            $name = str_replace('{id}', $suffix ?: 'detail', $name);
        } elseif ($suffix) {
            $name .= '.'.$suffix;
        }

        return trim($name, '.');
    }

    /**
     * 根据 HTTP 方法生成路由名称后缀
     *
     * - POST -> create
     * - PUT -> update
     * - DELETE -> delete
     * - 其他方法 -> 空字符串
     */
    protected function resolveNameSuffix(): string
    {
        return match ($this->method) {
            'POST' => 'create',
            'PUT' => 'update',
            'DELETE' => 'delete',
            default => ''
        };
    }

    /**
     * 解析 uri
     */
    protected function resolveUri(): string
    {
        $uri = trim($this->uri, '/');
        $prefix = Attribute::get(get_class($this->route), Prefix::class)?->value;

        return '/'.trim($prefix.'/'.$uri, '/');
    }
}
