<?php

declare(strict_types=1);

namespace Pin\Module;

use Pin\Module\Concerns\HasAction;
use Pin\Module\Concerns\HasController;
use Pin\Module\Concerns\HasDomain;
use Pin\Module\Concerns\HasFactory;
use Pin\Module\Concerns\HasModel;
use Pin\Module\Concerns\HasModule;
use Pin\Route\Routable;
use Pin\Support\Facades\RuntimeCache;

/**
 * 模块信息解析器
 */
class ModuleInspector
{
    use HasAction;
    use HasController;
    use HasDomain;
    use HasFactory;
    use HasModel;
    use HasModule;

    /**
     * 短类名
     */
    protected string $basename;

    /**
     * 类名片段
     *
     * @var list<string>
     */
    protected array $parts;

    /**
     * 类名
     */
    protected string $class;

    /**
     * 构造函数
     */
    public function __construct(string|Routable $class)
    {
        $this->class = is_string($class) ? ltrim($class, '\\') : $class::class;
        $this->parts = explode('\\', $this->class);
        $this->basename = array_last($this->parts);
    }

    /**
     * 获取类名对应的解析器
     */
    public static function make(string|Routable $class): static
    {
        $class = is_string($class) ? ltrim($class, '\\') : $class::class;

        return RuntimeCache::rememberForever(
            static::class.':'.$class,
            static fn () => app(static::class, ['class' => $class])
        );
    }

    /**
     * 导出模块信息
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
     * 返回首个存在的类，未找到时取最后一个候选
     *
     * @param  string[]  $candidates
     */
    protected function resolveFirstExistingClass(array $candidates): ?string
    {
        foreach ($candidates as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return array_last($candidates);
    }
}
