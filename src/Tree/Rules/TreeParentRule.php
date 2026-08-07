<?php

declare(strict_types=1);

namespace Pin\Tree\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Pin\Tree\TreeGuard;

/**
 * Tree 父节点合法性校验规则（Tree Parent Rule）
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
     * {@inheritDoc}
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = $this->guard->validatePid($this->id, (int) $value);
        if ($result !== true) {
            $fail($result);
        }
    }
}
