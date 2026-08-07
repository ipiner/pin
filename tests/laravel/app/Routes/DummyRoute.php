<?php

declare(strict_types=1);

namespace App\Routes;

use Pin\Route\Attributes\Prefix;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;

#[Prefix('/api/prefix/dummy/')]
enum DummyRoute: string implements Routable
{
    use InteractsWithRoute;

    case Index = 'GET:/';
}
