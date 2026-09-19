<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasFactory
{
    /**
     * 工厂类名
     */
    protected string $factory;

    /**
     * 解析工厂类名
     */
    public function factory(): string
    {
        return $this->factory ??= $this->resolveFirstExistingClass(
            $this->getFactoryCandidates()
        );
    }

    /**
     * 生成工厂候选类名。
     *
     * @return list<string>
     */
    protected function getFactoryCandidates(): array
    {
        $module = $this->module()['name'];
        $domain = $this->domain();

        $default = "Database\\Factories\\{$domain}Factory";

        if (! $module) {
            return [$default];
        }

        return [
            "Database\\Factories\\{$module}\\{$domain}Factory",
            "Database\\Factories\\{$module}{$domain}Factory",
            $default,
        ];
    }
}
