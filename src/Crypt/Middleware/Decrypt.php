<?php

declare(strict_types=1);

namespace Pin\Crypt\Middleware;

use Closure;
use Pin\Http\Middleware\TransformsRequest;
use Pin\Support\Facades\Aes;

/**
 * 解密中间件
 *
 * 自动对请求中的加密字段进行解密
 */
class Decrypt extends TransformsRequest
{
    /**
     * 执行入口
     */
    public function handle($request, Closure $next, string ...$fields)
    {
        $this->fields = $fields;

        return parent::handle($request, $next);
    }

    /**
     * 解密
     */
    protected function normalize(string $value): string
    {
        if ($plain = static::resolvePlainValue($value)) {
            return $plain;
        }

        return Aes::decrypt($value);
    }
}
