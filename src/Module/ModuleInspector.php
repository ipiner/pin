<?php

declare(strict_types=1);

namespace Pin\Module;

use Illuminate\Support\Arr;
use Pin\Route\Routable;
use Pin\Support\Facades\RuntimeCache;

/**
 * 解析应用类名并推导 Pin 模块元信息。
 *
 * 当调用方只需要按约定生成控制器、模型、工厂类名时，目标类本身不必真实存在。
 */
class ModuleInspector
{
    use Concerns\HasAction,
        Concerns\HasController,
        Concerns\HasDomain,
        Concerns\HasFactory,
        Concerns\HasModel,
        Concerns\HasModule;

    /**
     * 不含命名空间的短类名。
     */
    protected string $basename;

    /**
     * 按 `\` 拆分后的命名空间片段。
     *
     * @var list<string>
     */
    protected array $parts;

    /**
     * 类名
     */
    protected string $class;

    /**
     * @param  class-string|string  $class
     */
    public function __construct(string|Routable $class)
    {
        $this->class = is_string($class) ? $class : get_class($class);
        $this->basename = class_basename($this->class);
        $this->parts = explode('\\', $this->class);

    }

    /**
     * 为相同类名创建可复用的检查器实例。
     *
     * @param  class-string|Routable  $class
     */
    public static function make(string|Routable $class): static
    {
        $key = $class instanceof Routable ? get_class($class) : $class;

        return RuntimeCache::rememberForever(
            $key,
            fn () => app(static::class, ['class' => $class])
        );
    }

    /**
     * 导出已解析的全部模块元信息。
     *
     * @return array{
     *     basename: string,
     *     parts: list<string>,
     *     module: array{name: string|null, namespace: string|null},
     *     domain: string,
     *     controller: string,
     *     model: string,
     *     factory: string
     * }
     */
    public function toArray(): array
    {
        return [
            'basename' => $this->basename,
            'parts' => $this->parts,
            'module' => $this->module(),
            'domain' => $this->domain(),
            'controller' => $this->controller(),
            'model' => $this->model(),
            'factory' => $this->factory(),
        ];
    }

    /**
     * 从候选类列表中解析第一个存在的类名
     *
     * 如果全部不存在，则返回候选列表的最后一个作为兜底值
     */
    protected function resolveFirstExistingClass(array $candidates): ?string
    {
        $res = Arr::first($candidates, fn ($class) => class_exists($class));

        return $res ?? array_last($candidates);
    }
}
