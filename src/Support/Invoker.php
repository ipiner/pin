<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionProperty;

/**
 * 访问对象或类的非公开成员
 */
class Invoker
{
    protected ReflectionClass $reflection;

    /**
     * @param  class-string|object  $obj
     */
    public function __construct(protected string|object $obj)
    {
        $this->reflection = new ReflectionClass($obj);
    }

    /**
     * 调用方法
     */
    public function __call(string $method, array $args): mixed
    {
        $method = $this->reflection->getMethod($method);

        return $method->invokeArgs($method->isStatic() ? null : $this->getInstance(), $args);
    }

    /**
     * 获取属性值
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * 设置属性值
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * 获取属性值，支持点语法
     */
    public function get(string $name): mixed
    {
        [$name, $key] = $this->parse($name);
        $property = $this->prop($name);
        $value = $property->getValue($property->isStatic() ? null : $this->getInstance());

        return $key === null ? $value : Arr::get($value, $key);
    }

    /**
     * 设置属性值，支持点语法
     */
    public function set(string $name, mixed $value): void
    {
        [$name, $key] = $this->parse($name);
        $property = $this->prop($name);
        $instance = $property->isStatic() ? null : $this->getInstance();

        if ($key === null) {
            $property->setValue($instance, $value);

            return;
        }

        $data = $property->getValue($instance);
        Arr::set($data, $key, $value);
        $property->setValue($instance, $data);
    }

    /**
     * 获取实例，跳过构造函数
     */
    protected function getInstance(): object
    {
        if (is_string($this->obj)) {
            $this->obj = $this->reflection->newInstanceWithoutConstructor();
        }

        return $this->obj;
    }

    /**
     * 解析属性名和嵌套键
     *
     * @return array{string, string|null}
     */
    protected function parse(string $name): array
    {
        return str_contains($name, '.') ? explode('.', $name, 2) : [$name, null];
    }

    /**
     * 获取属性反射
     */
    protected function prop(string $name): ReflectionProperty
    {
        return new ReflectionProperty($this->obj, $name);
    }
}
