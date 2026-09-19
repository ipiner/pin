<?php

declare(strict_types=1);

namespace Pin\Route;

use Pin\Attributes\Attribute;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Prefix;

/**
 * 路由定义解析器
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
     * 路由 URI
     */
    public string $uri;

    public function __construct(protected Routable $route)
    {
        $this->resolve();
    }

    /**
     * 解析路由定义
     */
    protected function resolve(): void
    {
        $definition = explode('|', $this->route->value, 2);
        [$this->method, $this->uri] = explode(':', trim($definition[0]), 2);
        $this->method = strtoupper($this->method);
        $this->uri = $this->resolveUri();

        $this->name = trim($definition[1] ?? '');
        if ($this->name === '') {
            $this->name = $this->resolveName();
        }
    }

    /**
     * 自动生成路由名称
     */
    protected function resolveName(): string
    {
        $attribute = $this->route->attribute(Name::class);
        if ($attribute) {
            return $attribute->value;
        }

        $name = str_starts_with($this->uri, '/api/') ? substr($this->uri, 5) : $this->uri;
        $name = str_replace('/', '.', $name);

        $suffix = $this->resolveNameSuffix();
        if (str_contains($name, '{id}')) {
            $name = str_replace('{id}', $suffix ?: 'detail', $name);
        } elseif ($suffix) {
            $name .= '.'.$suffix;
        }

        return trim($name, '.');
    }

    /**
     * 根据 HTTP 方法生成路由名称后缀
     */
    protected function resolveNameSuffix(): string
    {
        return match ($this->method) {
            'POST' => 'create',
            'PUT' => 'update',
            'DELETE' => 'delete',
            default => '',
        };
    }

    /**
     * 解析路由 URI
     */
    protected function resolveUri(): string
    {
        $uri = trim($this->uri, '/');
        $prefix = Attribute::get($this->route::class, Prefix::class)?->value;

        return '/'.trim(trim((string) $prefix, '/').'/'.$uri, '/');
    }
}
