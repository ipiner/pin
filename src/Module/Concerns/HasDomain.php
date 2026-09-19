<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasDomain
{
    /**
     * 领域名称
     */
    protected string $domain;

    /**
     * 解析领域名称
     */
    public function domain(): string
    {
        return $this->domain ??= preg_replace(
            '/^(Create|Update)?(.+?)(Action|Service|Controller|Route)?$/',
            '$2',
            $this->basename
        );
    }
}
