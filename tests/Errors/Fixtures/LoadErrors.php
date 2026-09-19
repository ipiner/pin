<?php

declare(strict_types=1);

namespace Pin\Tests\Errors\Fixtures;

use Pin\Errors\Errorful;
use Pin\Errors\IError;

enum LoadErrors: string implements IError
{
    use Errorful;

    case Failed = '-20|Failed';
}
