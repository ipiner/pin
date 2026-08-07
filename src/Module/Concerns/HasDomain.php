<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasDomain
{
    /**
     * 从类名中提取出的领域名称。
     */
    protected string $domain;

    /**
     * 从 `CreateUserAction`、`UserRoute` 等类名中提取领域名称。
     */
    public function domain(): string
    {
        if (isset($this->domain)) {
            return $this->domain;
        }

        return $this->domain = preg_replace(
            '/(Create|Update)?(.+?)(Action|Service|Controller|Route)?$/',
            '$2',
            $this->basename
        );
    }
}
