<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

use Pin\Route\Routable;

trait HasAction
{
    /**
     * Action 类名缓存。
     *
     * @var array<string, string>
     */
    protected array $actions = [];

    /**
     * 解析 Action 类名
     */
    public function action(Routable $route): string
    {
        $key = $route::class.'::'.$route->name;

        return $this->actions[$key] ??= $this->resolveFirstExistingClass(
            $this->getActionCandidates($route)
        );
    }

    /**
     * 生成 Action 候选类名。
     *
     * @return array<int, string>
     */
    protected function getActionCandidates(Routable $route): array
    {
        $namespace = $this->module()['namespace'];
        $domain = $this->domain();
        $name = $route->name;

        if ($namespace) {
            $candidates = [
                "{$namespace}\\{$domain}\\Actions\\{$name}{$domain}Action",
                "{$namespace}\\{$domain}\\Actions\\{$name}Action",
                "{$namespace}\\Actions\\{$name}{$domain}Action",
                "{$namespace}\\Actions\\{$name}Action",
            ];
        } else {
            $candidates = [
                "App\\Modules\\{$domain}\\{$domain}Action",
                "App\\Actions\\{$domain}Action",
                "App\\Actions\\{$domain}\\{$name}{$domain}Action",
                "App\\Actions\\{$domain}\\{$name}Action",
            ];
        }

        return array_unique(array_filter(
            $candidates,
            static fn ($class) => ! str_ends_with($class, '\\Action')
        ));
    }
}
