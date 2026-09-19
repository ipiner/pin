<?php

declare(strict_types=1);

namespace Pin\Tree\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;
use Pin\Tree\TreeGuard;

/**
 * 父节点验证规则。
 */
class TreeParentRule implements ValidationRule
{
    /**
     * @param  int  $id  当前节点 ID
     */
    public function __construct(protected TreeGuard $guard, protected int $id)
    {
    }

    /**
     * 校验父节点。
     */
    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = $this->guard->validatePid($this->id, (int) $value);

        if ($result !== true) {
            $fail($result);
        }
    }
}
