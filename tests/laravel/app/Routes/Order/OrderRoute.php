<?php

declare(strict_types=1);

namespace App\Routes\Order;

use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Route\Attributes\Middleware;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;

enum OrderRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Middleware(TrimStrings::class)]
    case Index = 'GET:/api/orders';
    case Create = 'POST:/api/orders';
    case Delete = 'DELETE:/api/orders';

    protected function handler()
    {
        return function (Request $request) {
            return ApiResponse::make(message: $request->route()->getName());
        };
    }
}
