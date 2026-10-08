<?php

declare(strict_types=1);

namespace Pin\Validation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Pin\Validation\Rules\Password;

/**
 * 检测非空密码字段是否符合规范
 */
class ValidatePassword
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next, string ...$fields): mixed
    {
        $rules = $this->rules($request, $fields ?: ['password']);

        if ($rules) {
            Validator::make($request->all(), $rules)->validate();
        }

        return $next($request);
    }

    /**
     * 密码规范
     */
    protected function passwordRule(): Password
    {
        return new Password()->requiredCharacterTypes(2);
    }

    /**
     * 构造非空字段验证规则
     *
     * @param  list<string>  $fields
     * @return array<string, list<mixed>>
     */
    protected function rules(Request $request, array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            if (! $request->filled($field)) {
                continue;
            }

            $rules[$field] = [
                'bail',
                'string',
                $this->passwordRule(),
            ];
        }

        return $rules;
    }
}
