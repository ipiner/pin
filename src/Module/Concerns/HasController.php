<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasController
{
    /**
     * 控制器类名
     */
    protected string $controller;

    /**
     * 解析控制器类名
     */
    public function controller(): string
    {
        return $this->controller ??= $this->resolveFirstExistingClass(
            $this->getControllerCandidates()
        );
    }

    /**
     * 生成控制器候选类名
     *
     * @return list<string>
     */
    protected function getControllerCandidates(): array
    {
        $namespace = $this->module()['namespace'];
        $domain = $this->domain();

        if ($namespace) {
            return [
                "{$namespace}\\{$domain}Controller",
                "{$namespace}\\{$domain}\\{$domain}Controller",
            ];
        }

        return [
            "App\\Modules\\{$domain}\\{$domain}Controller",
            "App\\Http\\Controllers\\{$domain}Controller",
        ];
    }
}
