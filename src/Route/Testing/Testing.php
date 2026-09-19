<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Pin\Route\Routable;

/**
 * 路由测试
 */
class Testing
{
    use Concerns\HasAction;
    use Concerns\HasDomain;
    use Concerns\HasFactory;
    use Concerns\HasModel;
    use Concerns\HasPayload;
    use Concerns\HasReporter;
    use Concerns\HasRequest;

    /**
     * 初始化路由测试上下文
     */
    public function __construct(
        protected TestCase|TestbenchTestCase $testCase,
        public protected(set) Routable $route
    ) {
        $this->bootDomain();
        $this->bootAction();
        $this->bootFactory();
        $this->bootModel();
    }
}
