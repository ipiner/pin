<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionProperty;

/**
 * 一个通用工具类
 *
 * 动态访问对象或类的属性和方法，包括：
 * - 私有/受保护属性访问
 * - 静态属性访问
 * - 属性嵌套访问（支持点语法 "prop.key"）
 * - 方法调用（实例方法和静态方法）
 */
class Invoker
{
    /**
     * @param  class-string|object  $obj
     */
    public function __construct(protected string|object $obj)
    {
    }

    /**
     * 调用方法
     *
     * 支持 private / protected / static
     */
    public function __call(string $method, array $args): mixed
    {
        $ref = new ReflectionClass($this->obj);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        // 静态方法
        if ($m->isStatic()) {
            return $m->invokeArgs(null, $args);
        }

        // 实例方法
        return $m->invokeArgs($this->getInstance(), $args);
    }

    /**
     * 获取属性值
     *
     * 支持 private / protected / static / 点语法
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * 设置属性值
     *
     * 支持 private / protected / static / 点语法
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * 获取属性值
     *
     * 支持 private / protected / static / 点语法
     */
    public function get(string $name): mixed
    {
        [$prop, $key] = $this->parse($name);

        $p = $this->prop($prop);

        // 静态属性
        if ($p->isStatic()) {
            $value = $p->getValue();
        } else {
            $value = $p->getValue($this->getInstance());
        }

        return $prop === $key
            ? $value
            : Arr::get($value, $key);
    }

    /**
     * 设置属性值
     *
     * 支持 private / protected / static / 点语法
     */
    public function set(string $name, mixed $value): void
    {
        [$prop, $key] = $this->parse($name);

        $p = $this->prop($prop);

        if ($p->isStatic()) {
            $data = $p->getValue();

            if ($prop === $key) {
                $p->setValue(null, $value);
            } else {
                Arr::set($data, $key, $value);
                $p->setValue(null, $data);
            }

            return;
        }

        $instance = $this->getInstance();
        if ($prop === $key) {
            $p->setValue($instance, $value);

            return;
        }

        $data = $p->getValue($instance);
        Arr::set($data, $key, $value);
        $p->setValue($instance, $data);
    }

    /**
     * 获取实例
     */
    protected function getInstance(): object
    {
        return is_object($this->obj) ? $this->obj : new ReflectionClass($this->obj)->newInstanceWithoutConstructor();
    }

    /**
     * 解析点语法
     */
    protected function parse(string $name): array
    {
        return str_contains($name, '.') ? explode('.', $name, 2) : [$name, $name];
    }

    /**
     * 获取 ReflectionProperty（自动 accessible）
     */
    protected function prop(string $name): ReflectionProperty
    {
        $p = new ReflectionProperty($this->obj, $name);
        $p->setAccessible(true);

        return $p;
    }
}
