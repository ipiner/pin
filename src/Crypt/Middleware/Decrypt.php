<?php

declare(strict_types=1);

namespace Pin\Crypt\Middleware;

use Closure;
use Pin\Http\Middleware\TransformsRequest;
use Pin\Support\Facades\Aes;

/**
 * 请求字段解密。
 */
class Decrypt extends TransformsRequest
{
    /**
     * 处理请求。
     */
    public function handle($request, Closure $next, string ...$fields)
    {
        $this->fields = $fields;

        return parent::handle($request, $next);
    }

    /**
     * 解密字段。
     */
    protected function normalize(string $value): string
    {
        return static::resolvePlainValue($value) ?? Aes::decrypt($value);
    }
}
