<?php

declare(strict_types=1);

namespace Pin\Tests\Errors;

use Pin\Errors\Attribute\Group;
use Pin\Errors\Errorful;
use Pin\Errors\IError;

#[Group(false)]
enum Errors: string implements IError
{
    use Errorful;

    case DoesNothing = '-1|does nothing';
}

enum NoGroupErrors: string implements IError
{
    use Errorful;

    case Test = '-1|User Not Found';
}

#[Group('user')]
enum UserGroupErrors: string implements IError
{
    use Errorful;

    case Test = '-1|User Not Found';

    #[Group(false)]
    case DisabledFromCase = '-2|DisabledFromCase';

    #[Group('errors')]
    case GroupFromCase = '-3|GroupFromCase';

    #[Group(false)]
    case WithReplacement = '-12|Denied :name';
}

#[Group(false)]
enum DisabledGroupErrors: string implements IError
{
    use Errorful;

    case Test = '-1|User Not Found';

    case WithReplacement = '-11|Denied :name';
}

#[Group('custom_errors')]
enum OverrideErrors: string implements IError
{
    use Errorful;

    case ServerError = '500|503|server_error';

    #[Group(false)]
    case Forbidden = '403|409|Denied :name';

    #[Group(false)]
    case Unknown = '9999|404|Unknown :name';
}
