<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasFactory
{
    /**
     * 按命名约定解析出的工厂类名。
     */
    protected string $factory;

    /**
     * 解析第一个存在的工厂候选类
     *
     * 都不存在时返回最后一个兜底候选。
     */
    public function factory(): string
    {
        return $this->factory ??= $this->resolveFirstExistingClass(
            $this->getFactoryCandidates()
        );
    }

    /**
     * 从模块专属路径到 `Database\Factories` 依次生成工厂候选类名。
     *
     * @return list<class-string|string>
     */
    protected function getFactoryCandidates(): array
    {
        $module = $this->module()['name'];
        $domain = $this->domain();

        $candidates = [
            // 默认工厂：Database\Factories\CategoryFactory
            sprintf('Database\\Factories\\%sFactory', $domain),
        ];

        if (! $module) {
            return $candidates;
        }

        return [
            // 模块分组工厂：Database\Factories\Product\CategoryFactory
            sprintf('Database\\Factories\\%s\\%sFactory', $module, $domain),

            // 模块复合工厂：Database\Factories\ProductCategoryFactory
            sprintf('Database\\Factories\\%s%sFactory', $module, $domain),
            ...$candidates,
        ];
    }
}
