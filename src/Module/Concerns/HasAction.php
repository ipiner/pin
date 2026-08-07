<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

use Pin\Route\Routable;
use Pin\Support\Facades\RuntimeCache;

trait HasAction
{
    /**
     * 解析第一个存在的Action候选类
     *
     * 都不存在时返回最后一个兜底候选。
     */
    public function action(Routable $route): string
    {
        return RuntimeCache::rememberForever(
            __METHOD__.$route->value,
            fn () => $this->resolveFirstExistingClass(
                $this->getActionCandidates($route)
            )
        );
    }

    /**
     * 按从具体到通用的顺序生成Action候选类名。
     *
     * @return list<class-string|string>
     */
    protected function getActionCandidates(Routable $route): array
    {
        $namespace = $this->module()['namespace'];
        $domain = $this->domain();
        $caseName = $route->name;

        if ($namespace) {
            $candidates = [
                // App\Modules\Product\Category\Actions\CreateCategoryAction
                sprintf('%s\%s\Actions\%s%sAction', $namespace, $domain, $caseName, $domain),

                // App\Modules\Product\Category\Actions\CreateAction
                sprintf('%s\%s\Actions\%sAction', $namespace, $domain, $caseName),

                // App\Modules\Product\Actions\CreateProductAction
                sprintf('%s\Actions\%s%sAction', $namespace, $caseName, $domain),

                // App\Modules\Product\Actions\CreateAction
                sprintf('%s\Actions\%sAction', $namespace, $caseName),
            ];
        } else {
            $candidates = [
                // App\Modules\Login\LoginAction
                sprintf('App\\Modules\\%s\%sAction', $domain, $domain),
                // App\Actions\LoginAction
                sprintf('App\\Actions\\%sAction', $domain),
                // App\Actions\Product\CreateProductAction
                sprintf('App\\Actions\\%s\%s%sAction', $domain, $caseName, $domain),
                // App\Actions\Product\CreateAction
                sprintf('App\\Actions\\%s\%sAction', $domain, $caseName),
            ];
        }

        return array_unique(array_filter(
            $candidates,
            fn ($class) => ! str_ends_with($class, '\\Action')
        ));
    }
}
