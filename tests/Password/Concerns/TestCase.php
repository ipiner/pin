<?php

namespace Pin\Tests\Password\Concerns;

use Pin\Password\PasswordRule as BasePasswordRule;
use Pin\Support\Invoker;

trait TestCase
{
    protected PasswordRule $rule;

    protected Invoker $invoker;

    protected function setUpTestCase(): void
    {
        $this->rule = new PasswordRule();
        $this->invoker = $this->invoker($this->rule);
        $this->invoker->value = '123456';
    }
}

class PasswordRule extends BasePasswordRule
{
    public function value(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
