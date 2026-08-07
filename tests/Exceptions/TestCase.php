<?php

namespace Pin\Tests\Exceptions;

use Pin\Exceptions\Handler;
use Pin\Support\Invoker;

trait TestCase
{
    protected Handler $handler;

    protected Invoker $invoker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new Handler($this->app);
        $this->invoker = $this->invoker($this->handler);
    }
}
