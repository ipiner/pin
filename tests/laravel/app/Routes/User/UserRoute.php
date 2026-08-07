<?php

declare(strict_types=1);

namespace App\Routes\User;

use App\Modules\User\Actions\ListUsersAction;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pin\Route\Attributes\Action;
use Pin\Route\Attributes\Handler;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\TestingMethod;
use Pin\Route\Attributes\Title;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;

enum UserRoute: string implements Routable
{
    use InteractsWithRoute;

    // Index 默认 assertPaginated
    #[TestingMethod(\Pin\Route\Testing\TestingMethod::Successful)]
    #[Name('user.index')]
    #[Action(ListUsersAction::class)]
    case Index = 'GET:/';

    #[Title('用户列表')]
    #[Middleware(TrimStrings::class)]
    case List = 'GET:api/users//';

    case Create = 'POST:/api/users';
    case Update = 'PUT:/api/users/{id}';

    case Delete = 'DELETE:/api/users/{id}';

    #[Handler(['UserHandler', 'handle'])]
    case Handler = 'GET:/api/handler';

    public static function registerRoutes(): void
    {
        $action = function (Request $request, int $id = 0) {
            $data = [
                'id' => $id,
                'path' => $request->path(),
                'method' => $request->method(),
                'route' => Route::current(),
                'query' => $request->query(),
                'post' => $request->post(),
            ];

            return $request->expectsJson() ? ['data' => $data] : json_encode($data);
        };
        self::List->register($action, TrimStrings::class);
        self::Create->register($action);
        self::Update->register($action);
        self::Index->register($action);
    }
}
